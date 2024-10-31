<?php
/**
 * SecurePay for Restrict Content Pro.
 *
 * @author  SecurePay Sdn Bhd
 * @license GPL-2.0+
 *
 * @see    https://securepay.net
 */
\defined('ABSPATH') || exit;
if (empty($gateway)) {
    return;
}
?>
<tr class="pmpro_settings_divider gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <td colspan="2">
        <hr />
        <h2 class="title"><?php _e('SecurePay Settings', 'securepaypmpro'); ?></h2>
    </td>
</tr>

<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_testmode"><?php _e('Enable Test Mode', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="checkbox" value="1" name="securepay_testmode" id="securepay_testmode" <?php checked($values['securepay_testmode']); ?> />
        <label for="securepay_testmode"><?php _e('Check this to allow testing SecurePay without credentials.', 'securepaypmpro'); ?></label>
    </td>
</tr>

<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_banklist"><?php _e('Show Bank List', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="checkbox" value="1" name="securepay_banklist" id="securepay_banklist" <?php checked($values['securepay_banklist']); ?> />
        <label for="securepay_banklist"><?php _e('Check this to show bank list.', 'securepaypmpro'); ?></label>
    </td>
</tr>

<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_banklogo"><?php _e('Use Supported Bank Logo', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="checkbox" value="1" name="securepay_banklogo" id="securepay_banklogo" <?php checked($values['securepay_banklogo']); ?> />
        <label for="securepay_banklogo"><?php _e('Check this to use supported bank logo.', 'securepaypmpro'); ?></label>
    </td>
</tr>

<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_billing"><?php _e('Require billing address', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="checkbox" value="1" name="securepay_billing" id="securepay_billing" <?php checked($values['securepay_billing']); ?> />
        <label for="securepay_billing"><?php _e('Check this to enable billing address.', 'securepaypmpro'); ?></label>
    </td>
</tr>

<tr class="pmpro_settings_divider gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <td colspan="2">
        <hr />
    </td>
</tr>

<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_live_token"><?php _e('SecurePay Live Token', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="text" id="securepay_live_token" name="securepay_live_token" size="60" value="<?php echo esc_attr($values['securepay_live_token']); ?>" />
    </td>
</tr>
<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_live_checksum"><?php _e('SecurePay Live Checksum Token', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="text" id="securepay_live_checksum" name="securepay_live_checksum" size="60" value="<?php echo esc_attr($values['securepay_live_checksum']); ?>" />
    </td>
</tr>
<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_live_uid"><?php _e('SecurePay Live UID', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="text" id="securepay_live_uid" name="securepay_live_uid" size="60" value="<?php echo esc_attr($values['securepay_live_uid']); ?>" />
    </td>
</tr>
<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_live_partner_uid"><?php _e('SecurePay Live Partner UID', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="text" id="securepay_live_partner_uid" name="securepay_live_partner_uid" size="60" value="<?php echo esc_attr($values['securepay_live_partner_uid']); ?>" />
    </td>
</tr>

<tr class="pmpro_settings_divider gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <td colspan="2">
        <hr />
    </td>
</tr>

<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_sandboxmode"><?php _e('Enable Sandbox Mode', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="checkbox" value="1" name="securepay_sandboxmode" id="securepay_sandboxmode" <?php checked($values['securepay_sandboxmode']); ?> />
        <label for="securepay_sandboxmode"><?php _e('Check this to enable SecurePay Sandbox Mode.', 'securepaypmpro'); ?></label>
    </td>
</tr>

<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_sandbox_token"><?php _e('SecurePay Sandbox Token', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="text" id="securepay_sandbox_token" name="securepay_sandbox_token" size="60" value="<?php echo esc_attr($values['securepay_sandbox_token']); ?>" />
    </td>
</tr>
<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_sandbox_checksum"><?php _e('SecurePay Sandbox Checksum Token', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="text" id="securepay_sandbox_checksum" name="securepay_sandbox_checksum" size="60" value="<?php echo esc_attr($values['securepay_sandbox_checksum']); ?>" />
    </td>
</tr>
<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_sandbox_uid"><?php _e('SecurePay Sandbox UID', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="text" id="securepay_sandbox_uid" name="securepay_sandbox_uid" size="60" value="<?php echo esc_attr($values['securepay_sandbox_uid']); ?>" />
    </td>
</tr>
<tr class="gateway gateway_securepay" <?php
if ('securepay' !== $gateway) {
    ?> style="display: none;" <?php
}
?>>
    <th scope="row" valign="top">
        <label for="securepay_sandbox_partner_uid"><?php _e('SecurePay Sandbox Partner UID', 'securepaypmpro'); ?>:</label>
    </th>
    <td>
        <input type="text" id="securepay_sandbox_partner_uid" name="securepay_sandbox_partner_uid" size="60" value="<?php echo esc_attr($values['securepay_sandbox_partner_uid']); ?>" />
    </td>
</tr>
<script>
    jQuery( document ).ready( function() {
        jQuery( '#gateway' ).on( 'change', function() {
            jQuery( "select[name=currency]" ).parent().parent().prev().show();
            jQuery( "select#gateway_environment" ).parent().parent().show();

            if ( jQuery( this ).val() === 'securepay' ) {
                jQuery( "select[name=currency]" ).parent().parent().prev().hide();
                jQuery( "select#gateway_environment" ).parent().parent().hide();
            }
        } );

        if ( jQuery( '#gateway option:selected' ).val() === 'securepay' ) {
            jQuery( "select[name=currency]" ).parent().parent().prev().hide();
            jQuery( "select#gateway_environment" ).parent().parent().hide();
        }
    } );
</script>