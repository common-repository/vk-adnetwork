<?php
defined('ABSPATH') || exit;

/**
 * VK_Adnetwork_Admin_Ad_Type > ad_list_columns > ad_list_columns_placement
 * @var $ad
 */

$ad_pl_type['post_top'      ] = _x( 'post_top',       'placement_type', 'vk-adnetwork' ); // В верхней секции
$ad_pl_type['post_content'  ] = _x( 'post_content',   'placement_type', 'vk-adnetwork' ); // В контенте страницы
$ad_pl_type['post_bottom'   ] = _x( 'post_bottom',    'placement_type', 'vk-adnetwork' ); // В нижней секции
$ad_pl_type['footer'        ] = _x( 'footer',         'placement_type', 'vk-adnetwork' ); // Нижний колонтитул
$ad_pl_type['sidebar_widget'] = _x( 'sidebar_widget', 'placement_type', 'vk-adnetwork' ); // Виджет сайдбар
$ad_pl_type['default'       ] = _x( 'default',        'placement_type', 'vk-adnetwork' ); // Шорткод

?>
<span class="vk_adnetwork-ad-placement"><?php echo esc_html( $ad_pl_type[$ad->placement_type] ); ?>
    <?php echo esc_html($ad->placement_p); ?>
</span>
