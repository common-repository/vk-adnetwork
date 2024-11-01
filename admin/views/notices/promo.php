<?php
defined('ABSPATH') || exit;

/**
 * Template for a promotional banner
 *
 *  * VK_Adnetwork_Admin > plugins_loaded > wp_plugins_loaded > in_admin_header > register_admin_notices > admin_notices > admin_notices
 *  > VK_Adnetwork_Admin_Notices > display_notices
 *
 * @var string $text    content of the notice.
 * @var string $_notice internal key of the notice.
 */
?>
<div class="notice notice-promo vk_adnetwork-notice vk_adnetwork-admin-notice message is-dismissible"
    data-notice="<?php echo esc_attr( $_notice ); ?>"
    style="background: url(<?php echo esc_url( VK_ADNETWORK_BASE_URL . 'admin/assets/img/promo-background.svg' ); ?>);">
    <p>
        <?php
        echo wp_kses(
            $text,
            [
                'a' => [
                    'href' => [],
                    'class' => [],
                    'target' => [],
                ],
                'span' => [
                    'style' => [],
                ],
            ]
        );
        ?>
    </p>
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
    " class="notice-dismiss"><span class="screen-reader-text"><?php esc_html__( 'Dismiss this notice.', 'vk-adnetwork' ); ?></span></a>
</div>
