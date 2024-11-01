<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK_Adnetwork_Admin_Settings > settings_init > render_settings_uninstall_delete_data
 *
 * @var $enabled
 */
?>
<label class="checkbox">
  <input type="checkbox" value="1" name="<?php echo esc_attr( VK_ADNETWORK_SLUG ); ?>[vk-adnetwork-uninstall-delete-data]" <?php checked( $enabled, 1 ); ?>>
  <?php esc_html_e( 'Clean up all data related to VK AdNetwork when removing the plugin.', 'vk-adnetwork' ); ?>
</label>
<?php
