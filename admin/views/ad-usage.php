<?php
defined( 'ABSPATH' ) || exit;

/**
 * Render usage information for ads. // плашка Применение
 *
 * @var WP_Post $post current WP_Post object.
 * /admin/views/ad-usage-metabox.php
 *   /admin/includes/class-meta-box.php:172 ~ markup_meta_boxes($post, $box = 'ad-usage-box') ~ add_meta_boxes() ~ VK_Adnetwork_Admin_Meta_Boxes
 * @var $show_codes
 */
?>
<div id="vk_adnetwork-usage-shortcode-group" class="vk_adnetwork-usage-group" style="display:<?php echo esc_html($show_codes ? 'block' : 'none') ?>">
    <div class="separator no-indent" style="clear: both"></div>
    <label class="label" for="vk_adnetwork-usage-shortcode"><?php esc_html_e( 'Shortcode', 'vk-adnetwork' ); ?></label>
    <div class="vk_adnetwork-usage">
        <code><input type="text" id="vk_adnetwork-usage-shortcode" onclick="this.select();" value='[vk_adnetwork_the_ad id="<?php echo absint($post->ID); ?>"]' readonly/></code>
    </div>
    <div class="separator no-indent" style="clear: both"></div>
</div>
<div  id="vk_adnetwork-usage-function-group" class="vk_adnetwork-usage-group" style="display:<?php echo esc_html($show_codes ? 'block' : 'none') ?>">
    <label class="label" for="vk_adnetwork-usage-function"><?php esc_html_e( 'Template (PHP)', 'vk-adnetwork' ); ?></label>
    <div class="vk_adnetwork-usage">
        <code><input type="text" id="vk_adnetwork-usage-function" onclick="this.select();" value="&lt;?php vk_adnetwork_the_ad('<?php echo absint($post->ID); ?>'); ?&gt;" readonly/></code>
    </div>
    <div class="separator no-indent" style="clear: both"></div>
</div>
