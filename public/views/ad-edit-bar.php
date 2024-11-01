<?php
defined('ABSPATH') || exit;

/**
 * ... > get_ad_by_method > get_methods > get_ad_by_id > VK_Adnetwork_Ad::output > prepare_frontend_output > add_wrapper
 * @var $this
 */
?>
<div class="vk_adnetwork-edit-bar vk_adnetwork-edit-appear">
    <a href="<?php echo esc_url(get_edit_post_link( $this->id )); ?>"
       class="vk_adnetwork-edit-button"
       title="<?php echo esc_html( $this->title ); ?>"
       rel="nofollow"><span class="dashicons dashicons-edit"></span></a>
</div>
