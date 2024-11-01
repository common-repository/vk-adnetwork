<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK_Adnetwork_Overview_Widgets_Callbacks > setup_overview_widgets > add_meta_box
 *
 * @var $id
 * @var $content
 * @var $position
 * @var $title
 */
?>
<div id="<?php echo esc_attr( $id ); ?>" class="postbox position-<?php echo esc_attr( $position ); ?>">
    <!-- <?php if ( ! empty( $title ) ) : ?>
    <h2>
        <?php
        // phpcs:ignore
        echo esc_html($title);
        ?>
    </h2>
    <?php endif; ?> -->
    <div class="inside">
    <div class="main">
        <?php
        // phpcs:ignore
echo wp_kses($content,
        wp_kses_allowed_html( 'post' ) +
        [
            'input' => ['checked' => true,'type' => true, 'name' => true, 'value' => true ],
            'canvas' => ['id' => true, 'class' => true],
            'style' => true,
    ]);
        ?>
    </div>
    </div>
</div>
