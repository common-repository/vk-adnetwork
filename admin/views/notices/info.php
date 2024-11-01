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
<div class="notice notice-info vk_adnetwork-notice vk_adnetwork-admin-notice message is-dismissible" data-notice="<?php echo esc_attr( $_notice ); ?>">
    <?php echo wp_kses_post( $text ); ?>
    <a href="
    <?php
    add_query_arg(
        [
            'action'   => 'vk_adnetwork-close-notice',
            'notice'   => $_notice,
            'nonce'    => wp_create_nonce( 'vk-adnetwork-admin-ajax-nonce' ),
            'redirect' => sanitize_url($_SERVER['REQUEST_URI']),
        ],
        admin_url( 'admin-ajax.php' )
    );
    ?>
    " class="notice-dismiss"><span class="screen-reader-text"><?php esc_html__( 'Dismiss this notice.' ); ?></span></a>
</div>
