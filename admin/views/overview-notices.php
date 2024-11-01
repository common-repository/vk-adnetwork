<?php
defined( 'ABSPATH' ) || exit;

/**
 * Render box with problems and notifications on the VK AdNetwork overview page
 *
 * @var int $has_problems number of problems.
 * @var int $has_notices number of notices.
 * @var int $ignored_count number of ignored notices.
 */
?>
<h3 style="display:<?php echo esc_attr($has_problems ? 'block' : 'none'); ?>">
    <?php esc_attr_e( 'Problems', 'vk-adnetwork' ); ?>
</h3>
<?php VK_Adnetwork_Ad_Health_Notices::get_instance()->display_problems(); ?>
<h3 style="display:<?php echo esc_attr($has_notices ? 'block' : 'none'); ?>">
    <?php esc_attr_e( 'Notifications', 'vk-adnetwork' ); ?>
</h3>
<?php VK_Adnetwork_Ad_Health_Notices::get_instance()->display_notices(); ?>
<p class="vk_adnetwork-ad-health-notices-show-hidden" style="display:<?php echo esc_attr($ignored_count ? 'block' : 'none'); ?>">
    <?php
    echo wp_kses(
        sprintf(
            // translators: %s includes a number and markup like <span class="count">6</span>.
            esc_html__( 'Show %s hidden notices', 'vk-adnetwork' ),
            '<span class="count">' . absint( $ignored_count ) . '</span>'),
        ['span' => ['class' => true]]
    );
    ?>
    <button type="button"><span class="dashicons dashicons-visibility"></span></button>
</p>

<div class="vk_adnetwork-loader" style="display: none;"></div>
