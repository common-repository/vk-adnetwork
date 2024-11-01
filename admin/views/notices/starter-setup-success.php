<?php
defined('ABSPATH') || exit;

/**
 * VK_Adnetwork_Admin > wp_plugins_loaded > register_admin_notices
 *   > admin_notices (GET[message] === vk-adnetwork-starter-setup-success) > starter_setup_success_message
 *
 * @var $options
 * @var $wphost
 * @var $last_post_link
 * -x- div class="notice
 */
?>
<div class="<?php
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    echo esc_attr(($_REQUEST['message'] ?? '') === 'vk-adnetwork-starter-setup-success' ? 'notice setup-success-notice' : '');
?> notice-success vk_adnetwork-admin-notice message">
    <span>
        <?php printf( // Мы создали для вас первый рекламный блок. Можете настроить его или создать ещё — попробуйте! После того, как площадка пройдёт модерацию, опубликуйте рекламные блоки и начните зарабатывать. Подробнее
                // translators: %s is the address of the VK advertising network's help page
                wp_kses(__('We have created the first ad unit for you. You can customize it or create more - try it! After the site passes moderation, publish ad units and start earning. <a href="%s" target="_blank">More</a>', 'vk-adnetwork'), ['a' => ['href' => true]]),
                esc_url( admin_url( 'admin.php?page=vk-adnetwork-support' ) )
        ); ?>
    </span>
</div>