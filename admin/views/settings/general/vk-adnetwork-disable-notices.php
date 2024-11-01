<?php
/**
 * The view to render the option.
 *
 * @var int $disable_notices Value of 1, when the option is checked.
 */
?>
<label>
	<input id="vk-adnetwork-disabled-notices" type="checkbox" value="1" name="<?php echo esc_attr( VK_ADNETWORK_SLUG ); ?>[disable-notices]" <?php checked( $disable_notices, 1 ); ?>>
	<?php
    // Отключите Здоровье рекламы во фронтенде и бэкенде, предупреждения и внутренние уведомления - такие, как подсказки, руководства, новостные рассылки и уведомления об обновлениях.
	esc_html_e( 'Disable Ad Health in frontend and backend, warnings and internal notices like tips, tutorials, email newsletters and update notices.', 'vk-adnetwork' );
	?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ad_health' ) ); ?>" target="_blank" class="vk_adnetwork-manual-link">
		<?php esc_html_e( 'Manual', 'vk-adnetwork' ); ?>
	</a>
</label>
