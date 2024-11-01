<?php
defined( 'ABSPATH' ) || exit;

/**
 * Render the ad size column content in the ad list.
 *
 * VK_Adnetwork_Admin_Ad_Type > ad_list_columns > ad_list_columns_size
 *
 * @var string $size ad size string. Размер: width × height ( 900 × 250 )
 * @var string $status -- 'draft', 'publish', 'pending', 'future'
 */

$xstatus['draft'  ] = _x( 'draft',   'post_status', 'vk-adnetwork' ); // черновик
$xstatus['publish'] = _x( 'publish', 'post_status', 'vk-adnetwork' ); // активен
$xstatus['pending'] = _x( 'pending', 'post_status', 'vk-adnetwork' ); // на утверждении
$xstatus['future' ] = _x( 'future',  'post_status', 'vk-adnetwork' ); // запланирован
$xstatus['trash'  ] = _x( 'trash',   'post_status', 'vk-adnetwork' ); // корзина
?>
<span class="vk_adnetwork-ad-status"><?php echo esc_html( $xstatus[$status] ); ?></span>

