<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK_Adnetwork_Admin_Settings > settings_init > render_settings_hide_for_users
 * @var $roles
 * @var $hide_for_roles
 */
?>
<div id="vk_adnetwork-settings-hide-by-user-role__">
  <div class="text"><?php     // Вы можете отключить показ рекламы для администраторов, редакторов и авторов. Остальные пользователи будут видеть рекламу.
      echo wp_kses(__('You can turn off ad serving for administrators, editors, and authors. <br> Other users will see ads.', 'vk-adnetwork' ), ['br' => true]);
  ?></div>
  <div class="checkbox-group">
    <?php
        foreach ( $roles as $_role => $_display_name ) :
            $checked = in_array( $_role, $hide_for_roles, true );
            ?><label class="checkbox">
                <input type="checkbox" value="<?php echo esc_attr( $_role ); ?>" name="<?php echo esc_attr( VK_ADNETWORK_SLUG ); ?>[vk-adnetwork-hide-for-user-role][]"
                    <?php checked( $checked, true ); ?>
                ><?php echo esc_html( $_display_name ); ?>
            </label>
            <?php
        endforeach;
    ?>
  </div>
</div>
