<?php
defined('ABSPATH') || exit;

/**
 * VK_Adnetwork_Admin_Ad_Type > ad_list_columns > ad_list_columns_pad_id
 * @var $ad
 */
?>
<span class="vk_adnetwork-ad-placement"><u
            href="<?php echo esc_url(VK_ADNETWORK_URL); ?>pads/<?php echo absint($ad->pad_id); ?>/edit"
            title="<?php esc_html_e('Pad in VK AdNetwork', 'vk-adnetwork' ); ?>"
    ><?php echo absint($ad->slot_id); ?></u>
</span>
