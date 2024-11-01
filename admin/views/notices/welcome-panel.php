<?php
defined( 'ABSPATH' ) || exit;

$number_of_ads = 0;
// needed error handling due to a weird bug in the piklist plugin.
try {
  $number_of_ads = VK_Adnetwork::get_number_of_ads();
} catch ( Exception $e ) {
  // no need to catch anything since we just use TRY/CATCH to prevent an issue caused by another plugin.
}
?>
  <h2><?php // Добро пожаловать в VK AdNetwork!
    esc_attr_e( 'Welcome to VK AdNetwork!', 'vk-adnetwork' );
  ?></h2>

  <p>
    <?php
    printf( // Потребуется <a href="%s">зарегистрироваться</a> в рекламной сети <b>VK AdNetwork</b> и получить ключ доступа к API.
        // translators: %s is the address of the VK advertising network's help page
        wp_kses(__('You will need to <a href="%s">register</a> in the <b>VK AdNetwork</b> advertising network and get an API access key.', 'vk-adnetwork'), ['a' => ['href' => true], 'b' => true]),
        esc_url(VK_ADNETWORK_URL . 'partner')
    );
    ?>
  <br>
    <?php
    printf( // <a href="%s">Как это сделать</a>
        // translators: %s is the address of the VK advertising network's help page
        wp_kses(__('<a href="%s">How to do it</a>', 'vk-adnetwork'), ['a' => ['href' => true]]),
        esc_url(VK_ADNETWORK_URL . 'help/articles/partner_management_api')
    ); ?>
  </p>

  <a href="<?php echo esc_url( admin_url( '/admin.php?page=vk-adnetwork-settings' ) ); ?>"
    class="button button-primary"><?php
    // Внести ключи API
    esc_attr_e( 'Add API keys', 'vk-adnetwork' );
  ?></a>
