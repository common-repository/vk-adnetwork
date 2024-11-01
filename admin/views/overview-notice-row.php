<?php
defined( 'ABSPATH' ) || exit;

/**
 * Render a line in the notice meta box on the VK AdNetwork overview page
 *
 * overview-notice-row.php > VK_Adnetwork_Ad_Health_Notices > display_problems > display
 *
 * @var string $_notice_key index of the notice.
 * @var bool $is_hidden true if the notice is currently hidden
 * @var bool $can_hide true if the notice can be hidden
 * @var bool $hide true if the notice is hidden
 * @var string $date date string
 * @var string $text
 */
?>
<li class="vk_adnetwork-notice-inline" data-notice="<?php echo esc_attr( $_notice_key ); ?>" style="display:<?php echo esc_html($is_hidden ? 'none' : 'block'); ?>">
    <span>
        <?php
            // phpcs:ignore
            echo wp_kses($text, wp_kses_allowed_html( 'post' ));
        ?>
    </span>
    <?php if ( $can_hide ) : ?>
        <button type="button" class="vk_adnetwork-ad-health-notice-hide<?php echo esc_attr($hide ? '' : ' remove'); ?>"><span class="dashicons dashicons-no-alt"></span></button>
    <?php endif; ?>
    <?php if ( $date ) : ?>
        <span class="date"><?php echo esc_attr( $date ); ?></span>
    <?php endif; ?>
</li>
