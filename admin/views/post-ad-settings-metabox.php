<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK_Adnetwork_Admin_Meta_Boxes > add_post_meta_box > render_post_meta_box
 *
 * При редактировании страниц справа колонка
 *
 * @var $values
 */
?>
<label><input type="checkbox" name="vk_adnetwork[disable_ads]" value="1"
    <?php if ( isset( $values['disable_ads'] ) ) { checked( $values['disable_ads'], true ); } ?>
    /><?php echo wp_kses('<b>VK AdNetwork</b>: ' . esc_html__( 'Disable ads on this page', 'vk-adnetwork' ), ['b' => true]); // Не показывать объявления на этой странице
?></label>
