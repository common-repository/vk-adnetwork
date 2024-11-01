<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK_Adnetwork_Admin > plugins_loaded > wp_plugins_loaded > in_admin_header > register_admin_notices > admin_notices > admin_notices
 *  > VK_Adnetwork_Admin_Notices > display_notices
 *
 * @var $_notice
 * @var $text
 */
?>
<div class="notice notice-error vk_adnetwork-notice vk_adnetwork-admin-notice is-dismissible" data-notice="<?php echo esc_attr( $_notice ); ?>">
    <p><?php echo wp_kses_post( $text ); ?></p>
</div>
