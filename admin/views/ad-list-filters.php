<?php
defined( 'ABSPATH' ) || exit;

$ad_list_filters = VK_Adnetwork_Ad_List_Filters::get_instance();
$all_filters     = $ad_list_filters->get_all_filters();

$ad_size  = sanitize_text_field($_REQUEST['adsize'] ?? ''); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$ad_date  = sanitize_text_field($_REQUEST['addate'] ?? ''); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// hide the filter button. Can not filter correctly with "trashed" posts.
if ( isset( $_REQUEST['post_status'] ) && 'trash' === $_REQUEST['post_status'] ) {      // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    echo wp_kses('<style>#post-query-submit{display:none;}</style>', ['style' => true]);
}

?>

<?php if ( ! empty( $all_filters['all_sizes'] ) ) : ?>
    <select id="vk_adnetwork-filter-size" name="adsize">
        <option value="">- <?php esc_html_e( 'all ad sizes', 'vk-adnetwork' ); ?> -</option>
        <?php foreach ( $all_filters['all_sizes'] as $key => $value ) : ?>
            <option <?php selected( $ad_size, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
        <?php endforeach; ?>
    </select>
<?php endif; ?>

<?php if ( ! empty( $all_filters['all_dates'] ) ) : ?>
    <select id="vk_adnetwork-filter-date" name="addate">
        <option value="">- <?php esc_html_e( 'all ad dates', 'vk-adnetwork' ); ?> -</option>
        <?php foreach ( $all_filters['all_dates'] as $key => $value ) : ?>
            <option <?php selected( $ad_date, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
        <?php endforeach; ?>
    </select>
<?php endif; ?>

<?php do_action( 'vk-adnetwork-ad-list-filter-markup', $all_filters ); ?>
