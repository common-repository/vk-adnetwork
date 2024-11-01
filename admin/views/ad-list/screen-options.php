<?php
defined('ABSPATH') || exit;

/**
 * Screen Options for the ad list
 * #list-view-mode needs to be here to fix an issue were the list view mode cannot be reset automatically. Saving the form again does that.
 *
 * Параметры экрана для списка объявлений
 * #режим-просмотра-списка должен быть здесь, чтобы устранить проблему,
 * из-за которой режим просмотра списка не может быть сброшен автоматически.
 * Повторное сохранение формы делает это.
 *
 * VK_Adnetwork_Admin_Ad_Type > screen_settings > add_screen_options
 *
 * @var bool $show_filters
 */
?>
<input id="list-view-mode" type="hidden" name="mode" value="list">
<fieldset class="metabox-prefs vk_adnetwork-show-filter">
    <legend><?php esc_html_e( 'Filters', 'vk-adnetwork' ); ?></legend>
        <input id="vk_adnetwork-screen-options-show-filters" type="checkbox" name="vk-adnetwork-screen-options[show-filters]" value="true" <?php checked( $show_filters ); ?> />
        <label for="vk_adnetwork-screen-options-show-filters"><?php esc_html_e( 'Show filters permanently', 'vk-adnetwork' ); ?></label>
</fieldset>
<input type="hidden" name="vk-adnetwork-screen-options[sent]" value="true"/>
