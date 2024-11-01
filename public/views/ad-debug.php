<?php
defined('ABSPATH') || exit;

/**
 * ... > get_ad_by_method > get_methods > get_ad_by_id > VK_Adnetwork_Ad::output > VK_Adnetwork_Ad_Debug::prepare_debug_output
 *
 * @var $wrapper_id
 * @var $style
 * @var $content
 */
?>
<div id="<?php echo esc_attr($wrapper_id); ?>" style="<?php echo esc_attr($style); ?>">
<strong><?php esc_html_e( 'Ad debug output', 'vk-adnetwork' ); ?></strong>
<?php echo wp_kses('<br /><br />' . implode( '<br /><br />', $content ), ['br' => true]); ?>
<br /><br /><a style="color: green;" href="<?php echo esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#wp-debug' ) ) ?>" target="_blank" rel="nofollow"><?php esc_html_e( 'Find solutions in the manual', 'vk-adnetwork' ); ?></a>
</div>
