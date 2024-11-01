<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK_Adnetwork_Admin > wp_plugins_loaded > register_admin_notices > admin_notices > branded_admin_header
 *
 * Header on admin pages
 *
 * @var string    $title               page title.
 * @var WP_Screen $screen              Current screen
 * @var string    $reset_href          href attribute for the reset button
 * @var bool      $show_filter_button  if the filter button is visible
 * @var string    $filter_disabled     if the visible filter button is disabled
 * @var string    $new_button_label    text displayed on the New button
 * @var string    $new_button_href     href of the New button
 * @var string    $new_button_id       id of the New button
 * @var string    $back_button_label    text displayed on the back to list button
 * @var string    $back_button_href     href of the back to list button
 * @var string    $back_button_id       id of the back to list button
 * @var string    $show_screen_options if to show the Screen Options button
 * @var string    $manual_url          target of the manual link
 * @var string    $tooltip             description that will show in a tooltip
 */
?>
<div id="vk_adnetwork-header">
    <div id="vk_adnetwork-header-wrapper">
        <div class="vk_adnetwork-header-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewbox="0 0 24 24" fill="none"><path fill="#07F" d="M0 11.41c0-5.379 0-8.074 1.671-9.74C3.343.005 6.031 0 11.41 0h1.181c5.378 0 8.067 0 9.739 1.671C24 3.343 24 6.031 24 11.41v1.181c0 5.378 0 8.067-1.671 9.739C20.657 24 17.969 24 12.59 24h-1.18c-5.378 0-8.067 0-9.739-1.671C0 20.657 0 17.969 0 12.59v-1.18Z"></path><path fill="#00D3E6" d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z"></path><path fill="#fff" fill-rule="evenodd" d="M19.444 3.01v1.54h1.551c.197 0 .39.055.554.16a.984.984 0 0 1 .37.435.96.96 0 0 1-.2 1.065l-2.126 2.162a.999.999 0 0 1-.723.303h-2.01l-2.08 2.206c.159.371.24.77.237 1.173a2.91 2.91 0 0 1-.507 1.637c-.33.484-.8.862-1.35 1.085a3.067 3.067 0 0 1-1.739.167 3.029 3.029 0 0 1-1.54-.806 2.928 2.928 0 0 1-.823-1.508 2.89 2.89 0 0 1 .171-1.703 2.96 2.96 0 0 1 1.108-1.322 3.054 3.054 0 0 1 1.671-.496c.277 0 .551.036.817.108l2.39-2.568V5.026a.966.966 0 0 1 .315-.713l2.208-2.05a1.013 1.013 0 0 1 1.083-.183 1 1 0 0 1 .44.36c.107.16.165.346.166.537l.017.033Z" clip-rule="evenodd"></path></svg>
            <h1><?php echo esc_html( $title ); ?></h1>
        </div>
        <div id="vk_adnetwork-header-actions">
            <?php if ( $new_button_label !== '' ) : ?>
                <a href="<?php echo esc_url( $new_button_href ); ?>" class="header-action button vk_adnetwork-button-primary" id="<?php echo esc_attr( $new_button_id ); ?>">
                    <!-- <span class="dashicons dashicons-plus"></span> --><?php echo esc_html( $new_button_label ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $back_button_label !== '' ) : ?>
                <a href="<?php echo esc_url( $back_button_href ); ?>" class="header-action button vk_adnetwork-button-primary" id="<?php echo esc_attr( $back_button_id ); ?>">
                    <!-- <span class="dashicons dashicons-plus"></span> --><?php echo esc_html( $back_button_label ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $tooltip !== '' ) : ?>
                <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip"><?php echo esc_html( $tooltip ); ?></span></span>
            <?php endif; ?>
        </div>
        <div id="vk_adnetwork-header-links">
            <?php if ( $reset_href !== '' ) : ?>
                <a href="<?php echo esc_url( $reset_href ); ?>" class="button vk_adnetwork-button-secondary vk_adnetwork-button-icon-right">
                    <?php esc_html_e( 'Reset', 'vk-adnetwork' ); ?><span class="dashicons dashicons-undo"></span>
                </a>
            <?php endif; ?>
            <?php if ( $show_filter_button ) : ?>
                <a id="vk_adnetwork-show-filters" class="button vk_adnetwork-button-secondary vk_adnetwork-button-icon-right <?php echo esc_attr( $filter_disabled ); ?>">
                    <?php esc_html_e( 'Filters', 'vk-adnetwork' ); ?><span class="dashicons dashicons-filter"></span>
                </a>
            <?php endif; ?>
            <?php if ( $show_screen_options ) : ?>
                <a id="vk_adnetwork-show-screen-options" class="button vk_adnetwork-button-secondary"><?php esc_html_e( 'List options', 'vk-adnetwork' ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>
