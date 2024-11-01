<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK_Adnetwork_Admin > plugins_loaded > wp_plugins_loaded > in_admin_header > register_admin_notices > admin_notices > admin_notices
 */
$vk_adnetwork_ad_blocker_id = VK_Adnetwork_Plugin::get_instance()->get_frontend_prefix() ."abcheck-" . md5(microtime());
?>
<div id="<?php echo esc_html($vk_adnetwork_ad_blocker_id); ?>"
     class="message error update-message notice notice-alt notice-error" style="display: none;"><p><?php
        echo wp_kses( __( 'Please disable your <strong>AdBlocker</strong> to prevent problems with your ad setup.', 'vk-adnetwork' ), ['strong' => true]);
     ?></p></div>
<script>
jQuery( document ).ready( function() {
    if ( typeof vk_adnetwork_adblocker_test == 'undefined' ) {
        jQuery('#<?php echo esc_html($vk_adnetwork_ad_blocker_id); ?>.message').show();
    }
} );
</script>