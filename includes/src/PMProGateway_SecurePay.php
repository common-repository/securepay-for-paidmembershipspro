<?php
/**
 * SecurePay for Paid Memberships Pro.
 *
 * @author  SecurePay Sdn Bhd
 * @license GPL-2.0+
 *
 * @see    https://securepay.net
 */
\defined('ABSPATH') || exit;

class PMProGateway_SecurePay extends PMProGateway
{
    public function __construct($gateway = null)
    {
        $this->gateway = $gateway;

        return $this->gateway;
    }

    public static function init()
    {
        add_filter('pmpro_gateways', [__CLASS__, 'pmpro_gateways']);
        add_filter('pmpro_payment_options', [__CLASS__, 'pmpro_payment_options']);
        add_filter('pmpro_payment_option_fields', [__CLASS__, 'pmpro_payment_option_fields'], 10, 2);

        $gateway = pmpro_getGateway();
        if ('securepay' === $gateway) {
            add_action('pmpro_checkout_preheader', [__CLASS__, 'pmpro_checkout_preheader']);
            add_filter(
                'pmpro_include_billing_address_fields',
                function () {
                    if (1 === (int) pmpro_getOption('securepay_billing')) {
                        return true;
                    }

                    return false;
                }
            );
            add_filter('pmpro_required_billing_fields', [__CLASS__, 'pmpro_required_billing_fields']);
            add_filter('pmpro_include_payment_information_fields', '__return_false');
            add_filter('pmpro_checkout_default_submit_button', [__CLASS__, 'pmpro_checkout_default_submit_button']);
            add_filter('pmpro_checkout_before_change_membership_level', [__CLASS__, 'pmpro_checkout_before_change_membership_level'], 10, 2);

            add_filter(
                'pmpro_gateways_with_pending_status',
                function ($gw) {
                    $gw[] = 'securepay';

                    return $gw;
                }
            );
        }
    }

    public static function pmpro_gateways($gateways)
    {
        if (empty($gateways['securepay'])) {
            $gateways['securepay'] = __('SecurePay', 'securepaypmpro');
        }

        return $gateways;
    }

    public static function getGatewayOptions()
    {
        $options = [
             'securepay_banklist',
             'securepay_banklogo',
             'securepay_billing',
             'securepay_live_checksum',
             'securepay_live_partner_uid',
             'securepay_live_token',
             'securepay_live_uid',
             'securepay_sandbox_checksum',
             'securepay_sandboxmode',
             'securepay_sandbox_partner_uid',
             'securepay_sandbox_token',
             'securepay_sandbox_uid',
             'securepay_testmode',
         ];

        return $options;
    }

    public static function pmpro_payment_options($options)
    {
        $securepay_options = self::getGatewayOptions();

        $options = array_merge($securepay_options, $options);

        return $options;
    }

    public static function pmpro_payment_option_fields($values, $gateway)
    {
        include SECUREPAY_PMPRO_PATH.'/includes/admin/settings.php';
    }

    public static function pmpro_required_billing_fields($fields)
    {
        if (1 !== (int) pmpro_getOption('securepay_billing')) {
            unset($fields['bfirstname']);
            unset($fields['blastname']);
            unset($fields['baddress1']);
            unset($fields['bcity']);
            unset($fields['bstate']);
            unset($fields['bzipcode']);
            unset($fields['bphone']);
            unset($fields['bemail']);
            unset($fields['bcountry']);
        }
        unset($fields['CardType']);
        unset($fields['AccountNumber']);
        unset($fields['ExpirationMonth']);
        unset($fields['ExpirationYear']);
        unset($fields['CVV']);

        return $fields;
    }

    public static function pmpro_checkout_preheader()
    {
        global $gateway;

        $default_gateway = pmpro_getOption('gateway');

        if (('securepay' == $gateway || 'securepay' == $default_gateway)) {
            self::securepay_scripts();
        }
    }

    private static function securepay_scripts()
    {
        if (!is_admin()) {
            $version = SECUREPAY_PMPRO_VERSION.'x'.(\defined('WP_DEBUG') && WP_DEBUG ? time() : date('Ymdh'));
            $slug = SECUREPAY_PMPRO_SLUG;
            $url = SECUREPAY_PMPRO_URL;
            $selectid = 'securepayselect2';
            $selectdeps = [];
            if (wp_script_is('select2', 'enqueued')) {
                $selectdeps = ['jquery', 'select2'];
            } elseif (wp_script_is('selectWoo', 'enqueued')) {
                $selectdeps = ['jquery', 'selectWoo'];
            } elseif (wp_script_is($selectid, 'enqueued')) {
                $selectdeps = ['jquery', $selectid];
            }

            if (empty($selectdeps)) {
                wp_enqueue_style($selectid, $url.'includes/admin/min/select2.min.css', null, $version);
                wp_enqueue_script($selectid, $url.'includes/admin/min/select2.min.js', ['jquery'], $version);
                $selectdeps = ['jquery', $selectid];
            }

            wp_enqueue_script($slug, $url.'includes/admin/securepaypmpro.js', $selectdeps, $version);

            // remove jquery
            unset($selectdeps[0]);

            wp_enqueue_style($selectid.'-helper', $url.'includes/admin/securepaypmpro.css', $selectdeps, $version);
            wp_add_inline_script($slug, 'function securepaybankpmpro() { if ( "function" === typeof(securepaypmpro_bank_select) ) { securepaypmpro_bank_select(jQuery, "'.$url.'includes/admin/bnk/", '.time().', "'.$version.'"); }}');
        }
    }

    private static function get_bank_list($force = false)
    {
        $dosandbox = 1 === (int) pmpro_getOption('securepay_testmode') || 1 === (int) pmpro_getOption('securepay_sandboxmode') ? true : false;

        if (is_user_logged_in()) {
            $force = true;
        }

        $bank_list = $force ? false : get_transient(SECUREPAY_PMPRO_SLUG.'_banklist');
        $endpoint_pub = $dosandbox ? SECUREPAY_PMPRO_ENDPOINT_PUBLIC_SANDBOX : SECUREPAY_PMPRO_ENDPOINT_PUBLIC_LIVE;

        if (empty($bank_list)) {
            $remote = wp_remote_get(
                $endpoint_pub.'/banks/b2c?status',
                [
                    'timeout' => 10,
                    'user-agent' => SECUREPAY_PMPRO_SLUG.'/'.SECUREPAY_PMPRO_VERSION,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Referer' => home_url(),
                    ],
                ]
            );

            if (!is_wp_error($remote) && isset($remote['response']['code']) && 200 === $remote['response']['code'] && !empty($remote['body'])) {
                $data = json_decode($remote['body'], true);
                if (!empty($data) && \is_array($data) && !empty($data['fpx_bankList'])) {
                    $list = $data['fpx_bankList'];
                    foreach ($list as $arr) {
                        $status = 1;
                        if (empty($arr['status_format2']) || 'offline' === $arr['status_format1']) {
                            $status = 0;
                        }

                        $bank_list[$arr['code']] = [
                            'name' => $arr['name'],
                            'status' => $status,
                        ];
                    }

                    if (!empty($bank_list) && \is_array($bank_list)) {
                        set_transient(SECUREPAY_PMPRO_SLUG.'_banklist', $bank_list, 60);
                    }
                }
            }
        }

        return !empty($bank_list) && \is_array($bank_list) ? $bank_list : false;
    }

    private static function is_bank_list(&$bank_list = '')
    {
        if (1 === (int) pmpro_getOption('securepay_banklist')) {
            $bank_list = self::get_bank_list(false);

            return !empty($bank_list) && \is_array($bank_list) ? true : false;
        }

        $bank_list = '';

        return false;
    }

    private static function banklist_output()
    {
        $html = '';
        $bank_list = '';

        if (self::is_bank_list($bank_list)) {
            $bank_id = !empty($_POST['buyer_bank_code']) ? sanitize_text_field($_POST['buyer_bank_code']) : false;
            $image = false;
            if (1 === (int) pmpro_getOption('securepay_banklogo')) {
                $image = SECUREPAY_PMPRO_URL.'includes/admin/securepay-bank-alt.png';
            }

            $html = '<div id="spwfmbody-fpxbank" class="pmpro_checkout-field spwfmbody">';
            $html .= '<label for="buyer_bank_code">Pay with SecurePay</label>';

            if (!empty($image)) {
                $html .= '<img src="'.$image.'" class="spwfmlogo">';
            }

            $html .= '<select name="buyer_bank_code" id="buyer_bank_code">';
            $html .= "<option value=''>Please Select Bank</option>";
            foreach ($bank_list as $id => $arr) {
                $name = $arr['name'];
                $status = $arr['status'];

                $disabled = empty($status) ? ' disabled' : '';
                $offline = empty($status) ? ' (Offline)' : '';
                $selected = $id === $bank_id ? ' selected' : '';
                $html .= '<option value="'.$id.'"'.$selected.$disabled.'>'.$name.$offline.'</option>';
            }
            $html .= '</select>';
            $html .= '</div>';

            $html .= wp_get_inline_script_tag('if ( "function" === typeof(securepaybankpmpro()) ) {securepaybankpmpro();}', ['id' => SECUREPAY_PMPRO_SLUG.'-bankselect']);
        }

        return $html;
    }

    public static function pmpro_checkout_default_submit_button($show)
    {
        echo self::banklist_output();

        return true;
    }

    public static function pmpro_checkout_before_change_membership_level($user_id, $morder)
    {
        global $discount_code_id, $wpdb;

        //if no order, no need to pay
        if (empty($morder)) {
            return;
        }

        $morder->user_id = $user_id;
        $morder->saveOrder();

        //save discount code use
        if (!empty($discount_code_id)) {
            $wpdb->query('INSERT INTO `'.$wpdb->pmpro_discount_codes_uses."` (code_id, user_id, order_id, timestamp) VALUES('".esc_sql($discount_code_id)."', '".esc_sql($user_id)."', '".esc_sql($morder->id)."', now())");
        }

        do_action('pmpro_before_send_to_securepay', $user_id, $morder);

        $morder->Gateway->sendToSecurePay($morder);
    }

    public function process(&$order)
    {
        if (empty($order->code)) {
            $order->code = $order->getRandomCode();
        }

        $order->payment_type = 'SecurePay';
        $order->CardType = '';
        $order->cardtype = '';

        $order->status = 'review';
        $order->saveOrder();

        if (is_user_logged_in()) {
            return $this->sendToSecurePay($order);
        }

        return true;
    }

    private function sptokens()
    {
        $securepay_live_checksum = pmpro_getOption('securepay_live_checksum');
        $securepay_live_partner_uid = pmpro_getOption('securepay_live_partner_uid');
        $securepay_live_token = pmpro_getOption('securepay_live_token');
        $securepay_live_uid = pmpro_getOption('securepay_live_uid');
        $securepay_sandbox_checksum = pmpro_getOption('securepay_sandbox_checksum');
        $securepay_sandboxmode = pmpro_getOption('securepay_sandboxmode');
        $securepay_sandbox_partner_uid = pmpro_getOption('securepay_sandbox_partner_uid');
        $securepay_sandbox_token = pmpro_getOption('securepay_sandbox_token');
        $securepay_sandbox_uid = pmpro_getOption('securepay_sandbox_uid');
        $securepay_testmode = pmpro_getOption('securepay_testmode');

        if (1 === (int) $securepay_testmode) {
            $sp_payment_url = SECUREPAY_PMPRO_ENDPOINT_SANDBOX;
            $sp_token = 'GFVnVXHzGEyfzzPk4kY3';
            $sp_checksum = '3faa7b27f17c3fb01d961c08da2b6816b667e568efb827544a52c62916d4771d';
            $sp_uid = '4a73a364-6548-4e17-9130-c6e9bffa3081';
            $sp_partner_uid = '';
        } else {
            if (1 === (int) $securepay_sandboxmode) {
                $sp_payment_url = SECUREPAY_PMPRO_ENDPOINT_SANDBOX;
                $sp_token = $securepay_sandbox_token;
                $sp_checksum = $securepay_sandbox_checksum;
                $sp_uid = $securepay_sandbox_uid;
                $sp_partner_uid = $securepay_sandbox_uid;
            } else {
                $sp_payment_url = SECUREPAY_PMPRO_ENDPOINT_LIVE;
                $sp_token = $securepay_live_token;
                $sp_checksum = $securepay_live_checksum;
                $sp_uid = $securepay_live_uid;
                $sp_partner_uid = $securepay_live_uid;
            }
        }

        return (object) [
            'payment_url' => $sp_payment_url,
            'token' => $sp_token,
            'checksum' => $sp_checksum,
            'uid' => $sp_uid,
            'partner_uid' => $sp_partner_uid,
        ];
    }

    private function calculate_sign($checksum, $a, $b, $c, $d, $e, $f, $g, $h, $i)
    {
        $str = $a.'|'.$b.'|'.$c.'|'.$d.'|'.$e.'|'.$f.'|'.$g.'|'.$h.'|'.$i;

        return hash_hmac('sha256', $str, $checksum);
    }

    private function sanitize_response()
    {
        // fix response from api
        $req = $_SERVER['REQUEST_URI'];
        if (false !== strpos($req, 'securepay_return')) {
            $req = str_replace('&amp;', '&', $req);
            $req = str_replace('%26amp%3B', '&', $req);
            $req = str_replace('amp%3B', '&', $req);

            parse_str($req, $dataq);
            if (!empty($dataq)) {
                foreach ($dataq as $k => $v) {
                    $_REQUEST[$k] = $v;
                }
            }
        }

        $params = [
            'amount',
            'bank',
            'buyer_email',
            'buyer_name',
            'buyer_phone',
            'checksum',
            'client_ip',
            'created_at',
            'created_at_unixtime',
            'currency',
            'exchange_number',
            'fpx_status',
            'fpx_status_message',
            'fpx_transaction_id',
            'fpx_transaction_time',
            'id',
            'interface_name',
            'interface_uid',
            'merchant_reference_number',
            'name',
            'order_number',
            'payment_id',
            'payment_method',
            'payment_status',
            'receipt_url',
            'retry_url',
            'source',
            'status_url',
            'transaction_amount',
            'transaction_amount_received',
            'uid',
            'securepay_return',
        ];

        $response_params = [];
        if (isset($_REQUEST)) {
            foreach ($params as $k) {
                if (isset($_REQUEST[$k])) {
                    $response_params[$k] = sanitize_text_field($_REQUEST[$k]);
                }
            }
        }

        return $response_params;
    }

    private function response_status($response_params)
    {
        if ((isset($response_params['payment_status']) && 'true' === $response_params['payment_status']) || (isset($response_params['fpx_status']) && 'true' === $response_params['fpx_status'])) {
            return true;
        }

        return false;
    }

    private function is_response_callback($response_params)
    {
        if (isset($response_params['fpx_status'])) {
            return true;
        }

        return false;
    }

    public function sendToSecurePay(&$order)
    {
        $membership_id = $order->membership_id;
        $payment_id = $order->code;
        $amount = $order->total;

        $user_id = $order->user_id;
        $tokens = $this->sptokens();

        if (empty($tokens->checksum) || empty($tokens->uid) || empty($tokens->token)) {
            $error = esc_html__('Invalid SecurePay credentials, please verify SecurePay settings.', 'securepaypmpro');
            exit($error);
        }

        $user_name = '';
        $buyer_name = $order->Email;
        $buyer_email = $order->Email;
        $buyer_phone = !empty($order->billing->phone) ? $order->billing->phone : '';

        if (!empty($order->FirstName)) {
            $user_name = $order->FirstName;
        }

        if (!empty($order->LastName)) {
            $user_name = ' '.$order->LastName;
        }
        $user_name = trim($user_name);

        if (empty($user_name)) {
            $user_data = get_userdata($user_id);
            if (!empty($user_data)) {
                $user_name = '';

                if ($user_data->first_name) {
                    $user_name = $user_data->first_name;
                }

                if ($user_data->last_name) {
                    $user_name .= ' '.$user_data->last_name;
                }
                $user_name = trim($user_name);

                if (empty($user_name) && $user_data->user_nicename) {
                    $user_name = $user_data->user_nicename;
                }

                if (empty($buyer_email) && !empty($user_data->user_email)) {
                    $buyer_email = $user_data->user_email;
                }
            }
        }

        if (!empty($user_name)) {
            $buyer_name = $user_name;
        }

        $buyer_bank_code = !empty($_POST['buyer_bank_code']) ? sanitize_text_field($_POST['buyer_bank_code']) : false;
        $description = $order->membership_level->name.' at '.get_bloginfo('name');

        $query_args = 'timeout=0&cancel=0&level='.$order->membership_level->id.'&code='.$payment_id.'&cid='.$order->id.'&uid='.$user_id;
        $query_hash = base64_encode($query_args);
        $redirect_url = add_query_arg('securepay_return', $query_hash, get_bloginfo('url'));
        $callback_url = $redirect_url;

        $query_args = 'timeout=0&cancel=1&level='.$order->membership_level->id.'&code='.$payment_id.'&cid='.$order->id.'&uid='.$user_id;
        $query_hash = base64_encode($query_args);
        $cancel_url = add_query_arg('securepay_return', $query_hash, get_bloginfo('url'));

        $query_args = 'timeout=1&cancel=0&level='.$order->membership_level->id.'&code='.$payment_id.'&cid='.$order->id.'&uid='.$user_id;
        $query_hash = base64_encode($query_args);
        $timeout_url = add_query_arg('securepay_return', $query_hash, get_bloginfo('url'));

        $securepay_sign = $this->calculate_sign($tokens->checksum, $buyer_email, $buyer_name, $buyer_phone, $redirect_url, $payment_id, $description, $redirect_url, $amount, $tokens->uid);

        $securepay_args['order_number'] = esc_attr($payment_id);
        $securepay_args['buyer_name'] = esc_attr($buyer_name);
        $securepay_args['buyer_email'] = esc_attr($buyer_email);
        $securepay_args['buyer_phone'] = esc_attr($buyer_phone);
        $securepay_args['product_description'] = esc_attr($description);
        $securepay_args['transaction_amount'] = esc_attr($amount);
        $securepay_args['redirect_url'] = esc_url_raw($redirect_url);
        $securepay_args['callback_url'] = esc_url_raw($callback_url);
        $securepay_args['cancel_url'] = esc_url_raw($cancel_url);
        $securepay_args['timeout_url'] = esc_url_raw($timeout_url);
        $securepay_args['token'] = esc_attr($tokens->token);
        $securepay_args['partner_uid'] = esc_attr($tokens->partner_uid);
        $securepay_args['checksum'] = esc_attr($securepay_sign);
        $securepay_args['payment_source'] = 'paidmembershipspro';

        if (1 === (int) pmpro_getOption('securepay_banklist') && !empty($buyer_bank_code)) {
            $securepay_args['buyer_bank_code'] = esc_attr($buyer_bank_code);
        }

        $output = '<!doctype html><html><head><title>SecurePay</title>';
        $output .= '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">';
        $output .= '<meta http-equiv="Pragma" content="no-cache"><meta http-equiv="Expires" content="0">';
        $output .= '</head><body>';
        $output .= '<form name="order" id="securepay_payment" method="post" action="'.esc_url_raw($tokens->payment_url).'payments">';
        foreach ($securepay_args as $f => $v) {
            $output .= '<input type="hidden" name="'.$f.'" value="'.$v.'">';
        }

        $output .= '</form>';
        $output .= wp_get_inline_script_tag('document.getElementById( "securepay_payment" ).submit();');
        $output .= '</body></html>';
        exit($output);
    }

    private function redirect($redirect)
    {
        if (!headers_sent()) {
            wp_redirect($redirect);
            exit;
        }

        $html = "<script>window.location.replace('".$redirect."');</script>";
        $html .= '<noscript><meta http-equiv="refresh" content="1; url='.$redirect.'">Redirecting..</noscript>';

        echo wp_kses(
            $html,
            [
                'script' => [],
                'noscript' => [],
                'meta' => [
                    'http-equiv' => [],
                    'content' => [],
                ],
            ]
        );
        exit;
    }

    public function process_callback()
    {
        global $wpdb;
        $response_params = $this->sanitize_response();

        if (!empty($response_params) && !empty($response_params['securepay_return'])) {
            $hash = base64_decode($response_params['securepay_return']);
            if (false === $hash) {
                exit('failed to decode securepay_return');
            }

            parse_str($hash, $data);
            if (empty($data) || !\is_array($data) || empty($data['code']) || empty($data['cid']) || empty($data['uid'])) {
                exit('failed to decode securepay_return');
            }

            if (!empty($response_params['order_number'])) {
                $success = $this->response_status($response_params);

                $callback = $this->is_response_callback($response_params) ? 'Callback' : 'Redirect';
                $receipt_link = !empty($response_params['receipt_url']) ? $response_params['receipt_url'] : '';
                $status_link = !empty($response_params['status_url']) ? $response_params['status_url'] : '';
                $retry_link = !empty($response_params['retry_url']) ? $response_params['retry_url'] : '';

                $payment_id = $response_params['order_number'];
                $trans_id = !empty($response_params['merchant_reference_number']) ? $response_params['merchant_reference_number'] : '';

                if ($success) {
                    $note = 'SecurePay payment successful'.\PHP_EOL;
                    $note .= 'Response from: '.$callback.\PHP_EOL;
                    $note .= 'Transaction ID: '.$trans_id.\PHP_EOL;

                    if (!empty($receipt_link)) {
                        $note .= 'Receipt link: '.$receipt_link.\PHP_EOL;
                    }

                    if (!empty($status_link)) {
                        $note .= 'Status link: '.$status_link.\PHP_EOL;
                    }

                    $wpdb->update(
                        $wpdb->pmpro_membership_orders,
                        [
                            'status' => 'success',
                            'payment_transaction_id' => $trans_id,
                            'notes' => $note,
                        ],
                        [
                            'code' => $payment_id,
                            'id' => $data['cid'],
                        ]
                    );

                    pmpro_changeMembershipLevel($data['level'], $data['uid']);

                    $this->redirect(add_query_arg('level', $data['level'], pmpro_url('confirmation')));
                    exit;
                }

                $note = 'SecurePay payment failed'.\PHP_EOL;
                $note .= 'Response from: '.$callback.\PHP_EOL;
                $note .= 'Transaction ID: '.$trans_id.\PHP_EOL;

                if (!empty($retry_link)) {
                    $note .= 'Retry link: '.$retry_link.\PHP_EOL;
                }

                if (!empty($status_link)) {
                    $note .= 'Status link: '.$status_link.\PHP_EOL;
                }

                $wpdb->update(
                    $wpdb->pmpro_membership_orders,
                    [
                        'status' => 'error',
                        'payment_transaction_id' => $trans_id,
                        'notes' => $note,
                    ],
                    [
                        'code' => $payment_id,
                        'id' => $data['cid'],
                    ]
                );

                $this->redirect(add_query_arg('level', $data['level'], pmpro_url('checkout')));
                exit;
            }

            // cancel
            if (!empty($data['cancel'])) {
                $note = 'SecurePay payment cancelled'.\PHP_EOL;
                $wpdb->update(
                    $wpdb->pmpro_membership_orders,
                    [
                        'status' => 'cancelled',
                        'notes' => $note,
                    ],
                    [
                        'code' => $data['code'],
                        'id' => $data['cid'],
                    ]
                );
                $this->redirect(add_query_arg('level', $data['level'], pmpro_url('levels')));
                exit;
            }

            // timeout
            if (!empty($data['timeout'])) {
                $note = 'SecurePay payment timeout'.\PHP_EOL;
                $wpdb->update(
                    $wpdb->pmpro_membership_orders,
                    [
                        'status' => 'pending',
                        'notes' => $note,
                    ],
                    [
                        'code' => $data['code'],
                        'id' => $data['cid'],
                    ]
                );
                $this->redirect(add_query_arg('level', $data['level'], pmpro_url('checkout')));
                exit;
            }
        }
    }
}
