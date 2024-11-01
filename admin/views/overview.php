<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK AdNetwork overview page in the dashboard
 */

?>
<div class="wrap">
    <div id="vk_adnetwork-overview">
        <!-- <p>
            <?php // Больше информации про Сайт и рекламные блоки Вы можете получить в <a href="%s">личном кабинете VK AdNetwork</a>.
            printf(
            // translators: %s is the address of the VK advertising network server
            wp_kses( __( 'You can get more information about the Website and ad blocks in <a href="%s">VK AdNetwork personal account</a>.', 'vk-adnetwork' ), ['a' => ['href' => true]] ),
                esc_url(VK_ADNETWORK_URL . 'partner')
            ); ?>
        </p> -->
        <?php VK_Adnetwork_Overview_Widgets_Callbacks::setup_overview_widgets(); ?>
    </div>
    <?php do_action( 'vk-adnetwork-admin-overview-after' ); ?>
</div>
