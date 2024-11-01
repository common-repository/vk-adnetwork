<?php
defined( 'ABSPATH' ) || exit;

$types = VK_Adnetwork::get_instance()->ad_types; ?>
<?php
/**
 * When changing ad type ad parameter content is loaded via ajax
 *
 * плашка Параметры объявлений
 * -x- Вставьте простой текст или код в это поле.
 * § Разрешить PHP § Выполнить шорткоды § Размер ширина 300 px высота 600 px § зарезервировать это пространство
 *
 * @var $ad
 *
 * @filesource admin/assets/js/admin.js
 * @filesource includes/class-ajax-callbacks.php ::vk_adnetwork_load_ad_parameters_metabox
 * @filesource classes/ad-type-content.php :: renter_parameters()
 */
do_action( 'vk-adnetwork-ad-params-before', $ad, $types );
?>

<div id="vk-adnetwork-ad-parameters" class="vk_adnetwork-option-list">
    <?php
    $type = ( isset( $types[ $ad->type ] ) ) ? $types[ $ad->type ] : current( $types );
    $adcode = $type->render_parameters( $ad ); // classes/ad_type_plain.php:34 \VK_Adnetwork_Ad_Type_Plain::render_parameters

    include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-parameters-size.php'; // Размер ширина [0] px высота [0] px [_] зарезервировать это пространство
    ?>
    </div>
<?php

// extend the parameters form by ad type
if ( isset( $types[ $ad->type ] ) ) {
    do_action( "vk-adnetwork-ad-params-after-$ad->type", $ad, $types );
}

// extend the parameter form
do_action( 'vk-adnetwork-ad-params-after', $ad, $types );
