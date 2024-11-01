<?php
defined('ABSPATH') || exit;

/**
 * Render the view navigation items on the ad list.
 *
 * VK_Adnetwork_Ad_List_Filters > manage_posts_extra_tablenav > ad_views
 *
 * @var array $views_new                list of views.
 * @var bool  $show_trash_delete_button if the trash delete button is visible.
 */
?>
<ul class="vk-adnetwork-ad-list-views">
    <?php foreach ( $views_new as $class => $view ) : ?>
        <li class="button <?php echo esc_attr( $class ); ?>">
            <?php
            echo wp_kses( $view, [
                'a'    => [ 'href' => [] ],
                'span' => [ 'class' => [] ],
            ] );
            ?>
        </li>
    <?php endforeach; ?>
</ul>
<?php if ( $show_trash_delete_button ) : ?>
    <button type="submit" name="delete_all" id="delete_all" class="button vk_adnetwork-button-primary">
        <span class="dashicons dashicons-trash"></span><?php esc_html_e( 'Empty Trash', 'vk-adnetwork' ); ?>
    </button>
    <?php
endif;

