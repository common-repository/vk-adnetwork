<?php
defined( 'ABSPATH' ) || exit;

/**
 * The view for the settings page
 * VK_Adnetwork_Admin_Menu > add_plugin_admin_menu > display_plugin_settings_page
 * ('Регистрация в VK AdNetwork' : 'Settings') ?Авторизация
 *
 * @var $title2_settings_page
 * @var $newuser
 */

// array with setting tabs for frontend
// general
$setting_tabs = apply_filters(
  'vk-adnetwork-setting-tabs',
  [
    'general' => [
      'page'  => VK_Adnetwork_Admin::get_instance()->plugin_screen_hook_suffix,
      'group' => VK_ADNETWORK_SLUG,
      'tabid' => 'general',
      'title' => esc_html__( 'General', 'vk-adnetwork' ),
    ],
  ]
);

$support_message = $newuser
?
    sprintf( // Потребуется <a href="%s">зарегистрироваться</a> в рекламной сети <b>VK AdNetwork</b> и получить ключ доступа к API.
        // translators: %s is the address of the registration page of the VK advertising network
        __('You will need to <a href="%s">register</a> in the <b>VK AdNetwork</b> advertising network and get an API access key.', 'vk-adnetwork'),
       VK_ADNETWORK_URL . 'partner',
    )
  . '<br>'
  . sprintf( // <a href="%s">Как это сделать</a>
        // translators: %s is the address of the VK advertising network's help page
        __('<a href="%s">How to do it</a>', 'vk-adnetwork'),
        VK_ADNETWORK_URL . 'help/articles/partner_management_api'
    )
  . '<br>'
        // После получения ключей доступа введите их в поля ниже.
  . __('After receiving the access keys, enter them in the fields below.', 'vk-adnetwork')
  . '<br><b>N.B.</b> '
        // Когда вы заполните поля <b>client_id</b> и <b>client_secret</b> и нажмёте кнопку «Сохранить настройки на этой странице», то произойдет следующее:
  . __('When you fill in the fields <b>client_id</b> and <b>client_secret</b> and click "Save settings on this page", the following will happen:', 'vk-adnetwork')
  . '<ol><li>'
  . sprintf( // в рекламной сети <b>VK AdNetwork</b> будет создана площадка для домена <b>%s</b></li>
       // translators: %s is the wordpress domain where this plugin is located
       __('the advertising network <b>VK AdNetwork</b> will create a platform for the domain <b>%s</b></li>', 'vk-adnetwork'),
       VK_Adnetwork_Utils::vk_adnetwork_wphost()
      )
  . '</li><li>'
        // площадка должна пройти модерацию, прежде чем на ней будет показываться реклама (это около суток примерно)
  . __('the site must pass moderation before ads will be displayed on it (this is about a day approximately)', 'vk-adnetwork')
  . '</li><li>'
        // в площадке будет создан <b>рекламный блок</b> в формате inPage
  . __('<b>an advertising block</b> in the inPage format will be created in the site', 'vk-adnetwork')
  . '</li><li>'
        // в плагине будет создана <b>рекламная позиция</b> - код, который вызывает этот рекламный блок
  . __('a <b>advertising position</b> will be created in the plugin - the code that calls this ad block', 'vk-adnetwork')
  . '</li><li>'
        // эта рекламная позиция будет размещена на страницах вордпресса <b>над контентом</b> и <b>по центру</b>
  . __('this advertising position will be placed on the wordpress pages <b>above the content</b> and <b>in the center</b>', 'vk-adnetwork')
  . '</li><li>'
        // эта рекламная позиция будет в статусе <b>Черновик</b>
  . __('this advertising position will have the status <b>Draft</b>', 'vk-adnetwork')
  . '<br>'
        // (т.е., чтобы она стала видна на страницах вашего сайта, вам надо будет её <b>Опубликовать</b>)
  . __('(i.e., in order for it to become visible on the pages of your site, you will need it <b>Publish</b>)', 'vk-adnetwork')
  . '</li><li>'
        // перед публикацией вы можете изменить размещение и/или размеры вашего блока
  . __('before publishing, you can change the placement and/or size of your block', 'vk-adnetwork')
  . '</li><li>'
        // так же вы можете добавлять еще блоки с рекламой в других меcтах вашего сайта
  . __('you can also add more ad blocks in other places on your site', 'vk-adnetwork')
  . '</li></ol>'
:
    sprintf(
        // В случае возникновения технических проблем с плагином <b>VK AdNetwork</b> или с отображением рекламы обращайтесь в <a href="mailto:%s" target="_blank>службу поддержки</a>
        // translators: %s is the email of the support service and moderators of the VK advertising network
        __( 'In case of technical problems with plugin <b>VK AdNetwork</b> or with displaying ads, please contact <a href="mailto:%s" target="_blank">support service</a>', 'vk-adnetwork' ),
        'adnetwork_support@vk.company' // 'ads_moderation@vk.team'
    );

?><div class="wrap">
  <h2 style="display: none;"><!-- There needs to be an empty H2 headline at the top of the page so that WordPress can properly position admin notifications --></h2>
  <?php VK_Adnetwork_Checks::show_issues(); ?>

  <?php settings_errors(); ?>

  <?php foreach ( $setting_tabs as $_setting_tab_id => $_setting_tab ) : ?>

    <div id="vk_adnetwork-overview">
      <div id="vk_adnetwork_overview_support" class="postbox position-full">
        <!-- <h2><?php echo esc_html($title2_settings_page); ?></h2> -->
        <div class="inside">
          <div class="text"><?php
              echo wp_kses( $support_message,
                  ['a' => ['href' => true, 'target' => true], 'b' => true, 'br' => true, 'ol' => true, 'li' => true]
              )
              ?></div>
          <form class="vk_adnetwork-settings-tab-main-form" method="post" action="options.php">
          <?php
          if ( isset( $_setting_tab['group'] ) ) {
            settings_fields( $_setting_tab['group'] );
          }
          do_settings_sections( $_setting_tab['page'] );

          do_action( 'vk-adnetwork-settings-form', $_setting_tab_id, $_setting_tab );
          if ( isset( $_setting_tab['group'] )) {
            submit_button( esc_html__( 'Save settings on this page', 'vk-adnetwork' ) );
          }
          ?>
          </form>
          <?php do_action( 'vk-adnetwork-settings-tab-after-form', $_setting_tab_id, $_setting_tab ); ?>
          <?php /*
          // Скрываем ссылку на импорт \ экспорт, возможно, в будущем будет вновь востребована
          <?php if ( 'general' === $_setting_tab_id && !$newuser) : // новому юзеру не нужно видеть Import & Export ?>
          <ul class="list">
            <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=vk-adnetwork-import-export' ) ); ?>"><?php esc_html_e( 'Import &amp; Export', 'vk-adnetwork' ); ?></a></li>
          </ul>
          <?php endif; ?>
          */ ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php
    do_action( 'vk-adnetwork-additional-settings-form' );
    // print the filesystem credentials modal if needed.
    VK_Adnetwork_Filesystem::get_instance()->print_request_filesystem_credentials_modal();
  ?>

</div>
