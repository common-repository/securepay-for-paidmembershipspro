<?php
/**
 * SecurePay for Paid Memberships Pro.
 *
 * @author  SecurePay Sdn Bhd
 * @license GPL-2.0+
 *
 * @see    https://securepay.net
 */

/*
 * @wordpress-plugin
 * Plugin Name:         SecurePay for Paid Memberships Pro
 * Plugin URI:          https://www.securepay.my/?utm_source=wp-plugins-paidmembershipspro&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Version:             1.0.3
 * Description:         SecurePay payment platform plugin for Paid Memberships Pro
 * Author:              SecurePay Sdn Bhd
 * Author URI:          https://www.securepay.my/?utm_source=wp-plugins-paidmembershipspro&utm_campaign=author-uri&utm_medium=wp-dash
 * Requires at least:   5.4
 * Requires PHP:        7.2
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         securepaypmpro
 * Domain Path:         /languages
 */

if (!\defined('ABSPATH') || \defined('SECUREPAY_PMPRO_FILE')) {
    exit;
}

\define('SECUREPAY_PMPRO_VERSION', '1.0.3');
\define('SECUREPAY_PMPRO_SLUG', 'securepay-for-paidmembershipspro');
\define('SECUREPAY_PMPRO_ENDPOINT_LIVE', 'https://securepay.my/api/v1/');
\define('SECUREPAY_PMPRO_ENDPOINT_SANDBOX', 'https://sandbox.securepay.my/api/v1/');
\define('SECUREPAY_PMPRO_ENDPOINT_PUBLIC_LIVE', 'https://securepay.my/api/public/v1/');
\define('SECUREPAY_PMPRO_ENDPOINT_PUBLIC_SANDBOX', 'https://sandbox.securepay.my/api/public/v1/');
\define('SECUREPAY_PMPRO_FILE', __FILE__);
\define('SECUREPAY_PMPRO_HOOK', plugin_basename(SECUREPAY_PMPRO_FILE));
\define('SECUREPAY_PMPRO_PATH', realpath(plugin_dir_path(SECUREPAY_PMPRO_FILE)).'/');
\define('SECUREPAY_PMPRO_URL', trailingslashit(plugin_dir_url(SECUREPAY_PMPRO_FILE)));

require __DIR__.'/includes/load.php';
SecurePay_PMPro::attach();
