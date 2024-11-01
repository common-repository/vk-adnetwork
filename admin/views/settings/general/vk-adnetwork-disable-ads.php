<?php
defined( 'ABSPATH' ) || exit;

/**
 * Отключить рекламу (6 галочек: всю, 404, архив, вторичка, РСС, РЕСТ) -- оставили одну галочку
 * VK_Adnetwork_Admin_Settings > settings_init > render_settings_disable_ads
 *
 * @var $disable_all
 */
?>
<label class="checkbox"><input id="vk-adnetwork-disable-ads-all" type="checkbox" value="1" name="<?php echo esc_attr( VK_ADNETWORK_SLUG ); ?>[vk-adnetwork-disabled-ads][all]"
    <?php checked( $disable_all, 1 ); ?>
    ><?php
    // Отключить все объявления во фронтенде
    esc_html_e( 'Disable all ads in frontend', 'vk-adnetwork' );
    ?>
    <span class="vk_adnetwork-help">
        <span class="vk_adnetwork-tooltip">
            <?php
            // Включите эту опцию для того, чтобы отключить все объявления во фронтенде, но по-прежнему иметь возможность пользоваться плагином.
            esc_html_e( 'Use this option to disable all ads in the frontend, but still be able to use the plugin.', 'vk-adnetwork' );
            ?>
        </span>
    </span>
</label>


