<?php defined( 'ABSPATH' ) || exit; ?>
<div id="aa-moderation-panel">
    <h2><?php // options\moderation\issues === [GROUP_PAD_ON_MODERATION]
        // Площадка на модерации
        esc_attr_e( 'The platform is under moderation', 'vk-adnetwork' );
    ?></h2>
    <p>
        <?php
        printf( // Ключи успешно добавлены, осталось дождаться модерацию площадки. <a href="%s">Подробнее</a>
            // translators: %s is the address of the VK advertising network's help page
            wp_kses(__('The keys have been successfully added, it remains to wait for the moderation of the site. <a href="%s">Learn more</a>', 'vk-adnetwork'), ['a' => ['href' => true]]),
            esc_url(VK_ADNETWORK_URL . 'partner')
        );
        ?>
    <br>
        <?php
        printf( // Мы создали для вас первый рекламный блок. Можете настроить его или создать ещё — попробуйте! После того, как площадка пройдёт модерацию, опубликуйте рекламные блоки и начните зарабатывать. <a href="%s">Подробнее</a>
            // translators: %s is the address of the VK advertising network's help page
            wp_kses(__('We have created the first ad block for you. You can customize it or create another one — try it! After the platform passes moderation, publish the ad blocks and start earning. <a href="%s">Learn more</a>', 'vk-adnetwork'), ['a' => ['href' => true]]),
            esc_url(VK_ADNETWORK_URL . 'help/articles/partner_management_api')
        ); ?>
    </p>
</div>
