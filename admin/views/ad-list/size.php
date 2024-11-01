<?php
defined('ABSPATH') || exit;

/**
 * Render the ad size column content in the ad list.
 *
 * VK_Adnetwork_Admin_Ad_Type > ad_list_columns > ad_list_columns_size
 *
 * @var string $size ad size string. Размер: width × height ( 900 × 250 )
 */
?>
<span class="vk_adnetwork-ad-size"><?php echo esc_html( $size ); ?></span>

