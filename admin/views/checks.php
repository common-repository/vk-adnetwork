<?php
defined( 'ABSPATH' ) || exit;

/**
 * A couple of checks to see if there is any critical issue
 * listed on support and settings page
 */

$messages = [];

if ( VK_Adnetwork_Ad_Health_Notices::has_visible_problems() ) {
    $messages[] = sprintf(
        // translators: %1$s is a starting link tag, %2$s is closing the link tag.
        esc_html__( 'VK AdNetwork detected potential problems with your ad setup. %1$sShow me these errors%2$s', 'vk-adnetwork' ),
        '<a href="' . admin_url( 'admin.php?page=vk-adnetwork' ) . '">',
        '</a>'
    );
}

$messages = apply_filters( 'vk-adnetwork-support-messages', $messages );

if ( count( $messages ) ) :
    ?>
    <div class="message error">
        <?php foreach ( $messages as $_message ) : ?>
            <p>
                <?php
                // phpcs:ignore
                echo wp_kses($_message, ['a' => ['href' => true]]);
                ?>
            </p>
        <?php endforeach; ?>
    </div>
    <?php
endif;
