<?php
defined('ABSPATH') || exit;

/**
 * Render the "Notes" column content in the ad list.
 *
 * VK_Adnetwork_Admin_Ad_Type > ad_list_columns > ad_list_columns_description
 *
 * @var string $description ad description.
 */
?>
<div class="vk_adnetwork-ad-list-description"><?php echo esc_html( $description ); ?></div>
