<?php defined( 'ABSPATH' ) || exit; ?>
<div id="aa-moderation-panel">
    <h2><?php // options\moderation\issues === []
        // Поздравляем! Модерация пройдена
        esc_attr_e( 'Congratulations! Moderation passed', 'vk-adnetwork' );
    ?></h2>
    <p>
        <?php
        printf( // Опубликуйте ранее созданные рекламные блоки — начните зарабатывать. <a href="%s">Как это сделать</a>
            // translators: %s is the address of the VK advertising network's help page
            wp_kses(__('Publish previously created ad blocks — start earning. <a href="%s">How to do it</a>', 'vk-adnetwork'), ['a' => ['href' => true]]),
            esc_url(VK_ADNETWORK_URL . 'partner')
        );
        ?>
    </p>
</div>
