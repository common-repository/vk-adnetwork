<?php
defined( 'ABSPATH' ) || exit;

/**
 * Option to enter notes for a given ad
 *
 * @var VK_Adnetwork_Ad $ad Ad object.
 */
?>
<?php /*
<label class="label" for="vk_adnetwork-usage-notes" onclick="vk_adnetwork_toggle('#vk_adnetwork-ad-notes textarea'); vk_adnetwork_toggle('#vk_adnetwork-ad-notes p')"><?php
    esc_html_e( 'Notes', 'vk-adnetwork' );
    ?></label>
*/ ?>
<div id="vk_adnetwork-ad-notes">
<?php /*<div class="js-vk_adnetwork-ad-notes-title" title="<?php esc_html_e( 'click to change', 'vk-adnetwork' ); ?>" onclick="vk_adnetwork_toggle('#vk_adnetwork-ad-notes textarea');">
        <?php
        if ( ! empty( $ad->description ) ) {
            echo nl2br( esc_html( $ad->description ) );
        } else {
            esc_html_e( 'Click to add notes', 'vk-adnetwork' ); // Нажмите, чтобы добавить заметки
        }
        ?>
        <span class="dashicons dashicons-edit"></span>
    </div>
*/ ?>
    <?php wp_nonce_field( 'vk_adnetwork-save-ad-' . $ad->id, 'vk_adnetwork_save_nonce'); ?>
    <textarea name="vk_adnetwork[description]" id="vk_adnetwork-usage-notes"><?php echo esc_html( $ad->description ); ?></textarea>
</div>
<!-- <div class="separator no-indent"></div> -->
