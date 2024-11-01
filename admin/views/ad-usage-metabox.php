<?php
defined( 'ABSPATH' ) || exit;

/**
 * Render the Usage meta box on the ad edit screen
 * /admin/includes/class-meta-box.php:172 ~ markup_meta_boxes($post, $box = 'ad-usage-box')
 */
?>
<div id="vk_adnetwork-ad-usage" class="vk_adnetwork-option-list">
    <?php
    include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-usage-notes.php'; // Нажмите, чтобы добавить заметки
    include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-usage.php';       // [vk_adnetwork_the_ad id="post-ID"] + {?php vk_adnetwork_the_ad(post-ID) ?}
    ?>
</div>
