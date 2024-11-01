<?php
/**
 * Container class for custom filters on admin ad list page.
 *
 * @package WordPress
 * @subpackage VK AdNetwork Plugin
 */
class VK_Adnetwork_Ad_List_Filters {
    /**
     * The unique instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Ads data for the ad list table
     *
     * @var     array
     */
    protected $all_ads = [];

    /**
     * Ads array with ID as key
     *
     * @var     array
     * @deprecated 1.31.0 -- we don't needs ads indexed by id, since we have all ads.
     */
    protected $adsbyid = [];

    /**
     * All filters available in the current ad list table
     *
     * @var     array
     */
    protected $all_filters = [];

    /**
     * All ad options for the ad list table
     *
     * @var     array
     */
    protected $all_ads_options = [];

    /**
     * Constructs the unique instance.
     */
    private function __construct() {
        if ( is_admin() && ! wp_doing_ajax() ) {
            add_filter( 'posts_results', [ $this, 'post_results' ], 10, 2 );
            add_filter( 'post_limits', [ $this, 'limit_filter' ], 10, 2 );
        }

        add_filter( 'views_edit-' . VK_Adnetwork::POST_TYPE_SLUG, [ $this, 'add_expired_view' ] );
        add_filter( 'views_edit-' . VK_Adnetwork::POST_TYPE_SLUG, [ $this, 'add_expiring_view' ] );
        add_action( 'restrict_manage_posts', [ $this, 'send_addate_in_filter' ] );

        add_action( 'manage_posts_extra_tablenav', [ $this, 'ad_views' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'ad_list_scripts' ], 11 );
    }

    /**
     * Return true if the current screen is the ad list.
     *
     * @return bool
     */
    private function is_ad_list_screen() {
        $screen = get_current_screen();
        return isset( $screen->id ) && $screen->id === 'edit-vk_adnetwork'; // ads overview. /wp-admin/edit.php?post_type=vk_adnetwork
    }

    /**
     * Check if the current screen uses a search or filter.
     *
     * @return bool
     */
    public static function uses_filter_or_search() {
        return ! empty( $_GET['s'] ) || ! empty( $_GET['adsize'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Collect available filters for ad overview page.
     *
     * @param array $posts array of ads.
     *
     * @return null
     */
    private function collect_filters( $posts ) {
        $all_sizes  = [];
        $all_dates  = [];

        $all_filters = [
            'all_sizes'  => [],
            'all_dates'  => [],
        ];

        // can not filter correctly with "trashed" posts. Do not display any filtering option in this case.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] === 'trash' ) {
            $this->all_filters = $all_filters;

            return;
        }

        foreach ( $posts as $post ) {
            $ad_option = $this->all_ads_options[ $post->ID ];

            if ( isset( $ad_option['width'], $ad_option['height'] ) && $ad_option['width'] && $ad_option['height'] ) {
                if ( ! array_key_exists( $ad_option['width'] . 'x' . $ad_option['height'], $all_filters['all_sizes'] ) ) {
                    $all_filters['all_sizes'][ $ad_option['width'] . 'x' . $ad_option['height'] ] = $ad_option['width'] . ' x ' . $ad_option['height'];
                }
            }

        }

        $this->all_filters = $all_filters;
    }

    /**
     * Collects all ads data.
     *
     * @param WP_Post[] $posts array of ads.
     */
    public function collect_all_ads( $posts ) {
        foreach ( $posts as $post ) {
            $this->adsbyid[ $post->ID ]         = $post;
            $this->all_ads_options[ $post->ID ] = get_post_meta( $post->ID, 'vk_adnetwork_ad_options', true );
            if ( empty( $this->all_ads_options[ $post->ID ] ) ) {
                $this->all_ads_options[ $post->ID ] = [];
            }

            // convert all expiration dates.
            $ad         = new VK_Adnetwork_Ad( $post->ID );
            $expiration = new VK_Adnetwork_Ad_Expiration( $ad );
            $expiration->save_expiration_date( $this->all_ads_options[ $post->ID ], $ad );
            $expiration->is_ad_expired();
        }

        $this->all_ads = $posts;
    }

    /**
     * Retrieve all filters that can be applied.
     */
    public function get_all_filters() {
        return $this->all_filters;
    }

    /**
     * Remove limits because we need to get all ads.
     *
     * @param string   $limits The LIMIT clause of the query.
     * @param WP_Query $the_query the current WP_Query object.
     * @return string $limits The LIMIT clause of the query.
     */
    public function limit_filter( $limits, $the_query ) {
        // Execute only in the main query.
        if ( ! $the_query->is_main_query() ) {
            return $limits;
        }

        if ( ! function_exists( 'get_current_screen' ) ) {
            return $limits;
        }

        $scr = get_current_screen();
        // Execute only in the ad list page.
        if ( ! $scr || 'edit-vk_adnetwork' !== $scr->id ) { // ads overview. /wp-admin/edit.php?post_type=vk_adnetwork
            return $limits;
        }

        return '';
    }

    /**
     * Edit the query for list table.
     *
     * @param array    $posts the posts array from the query.
     * @param WP_Query $the_query the current WP_Query object.
     *
     * @return array with posts
     */
    public function post_results( $posts, $the_query ) {
        // Execute only in the main query.
        if ( ! function_exists( 'get_current_screen' ) || ! $the_query->is_main_query() ) {
            return $posts;
        }

        $scr = get_current_screen();
        // Execute only in the ad list page.
        if ( ! $scr || 'edit-vk_adnetwork' !== $scr->id ) { // ads overview. /wp-admin/edit.php?post_type=vk_adnetwork
            return $posts;
        }

        // Searching an ad ID.
        if ( (int) $the_query->query_vars['s'] !== 0 ) {
            global $wpdb;
            $single_ad = ( new VK_Adnetwork_Model( $wpdb ) )->get_ads(
                [
                    'p'           => (int) $the_query->query_vars['s'],
                    'post_status' => [ 'any' ],
                ]
            );

            if ( ! empty( $single_ad ) ) {
                // Head to the ad edit page if one and only one ad found.
                if ( empty( $posts ) ) {
                    wp_safe_redirect( add_query_arg( [
                        'post'   => $single_ad[0]->ID,
                        'action' => 'edit',
                    ], admin_url( 'post.php' ) ) );
                    die;
                }

                if ( ! in_array( $single_ad[0]->ID, wp_list_pluck( $posts, 'ID' ), true ) ) {
                    $posts[] = $single_ad[0];
                }
            }
        }

        $this->collect_all_ads( $posts );

        // the new post list.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_REQUEST['post_status'] ) && 'trash' === $_REQUEST['post_status'] ) {
            // if looking in trash, return the original trashed posts list.
            $new_posts = $posts;
        } else {
            // in other cases, apply our custom filters.
            $new_posts = $this->ad_filters( $this->all_ads, $the_query );
        }

        $per_page = $the_query->query_vars['posts_per_page'] ?: 20;

        if ( $per_page < count( $new_posts ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $paged                  = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
            $total                  = count( $new_posts );
            $new_posts              = array_slice( $new_posts, ( $paged - 1 ) * $per_page, $per_page );
            $the_query->found_posts = $total;
            $the_query->post_count  = count( $new_posts );
        }

        // replace the post list.
        $the_query->posts = $new_posts;

        return $new_posts;
    }

    /**
     * Apply ad filters on post array
     *
     * @param array $posts the original post array.
     * @param WP_Query $the_query the current WP_Query object.
     *
     * @return array with posts
     */
    private function ad_filters(array $posts, WP_Query $the_query ): array
    {
        $using_original = true;
        $post_status = sanitize_text_field( $_REQUEST['post_status'] ?? '' );   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $author = absint( $_REQUEST['author'] ?? 0 );                           // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $adsize = sanitize_text_field( $_REQUEST['adsize'] ?? '' );             // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $addate = sanitize_text_field( $_REQUEST['addate'] ?? '' );             // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        /**
         *  Filter post status
         */
        if ( '' !== $post_status && ! in_array( $post_status, [ 'all', 'trash' ], true ) ) {
            $new_posts = [];
            foreach ( $this->all_ads as $post ) {
                if ( $post_status === $post->post_status ) {
                    $new_posts[] = $post;
                }
            }
            $posts                  = $new_posts;
            $the_query->found_posts = count( $posts );
            $using_original         = false;
        }

        /**
         *  Filter post author
         */
        if ( 0 !== $author ) {
            $new_posts = [];
            $the_list  = $using_original ? $this->all_ads : $posts;
            foreach ( $the_list as $post ) {
                if ( absint( $post->post_author ) === $author ) {
                    $new_posts[] = $post;
                }
            }
            $posts                  = $new_posts;
            $the_query->found_posts = count( $posts );
            $using_original         = false;
        }

        /**
         * Filter ad size
         */
        if ( '' !== $adsize ) {
            $new_posts = [];
            $the_list  = $using_original ? $this->all_ads : $posts;
            foreach ( $the_list as $post ) {
                $option = $this->all_ads_options[ $post->ID ];
                if ( $adsize !== 'responsive' ) {
                    $width  = $option['width'] ?? 0;
                    $height = $option['height'] ?? 0;
                    if ( $adsize === $width . 'x' . $height ) {
                        $new_posts[] = $post;
                    }
                }
            }
            $posts                  = $new_posts;
            $the_query->found_posts = count( $posts );
            $using_original         = false;
        }

        if ( $addate ) {
            if ( in_array( $addate, [ 'vk_adnetwork-filter-expired', 'vk_adnetwork-filter-expiring' ], true ) ) {
                $posts = $this->filter_expired_ads( $addate, $using_original ? $this->all_ads : $posts );
            }
        }

        $posts                  = apply_filters( 'vk-adnetwork-ad-list-filter', $posts, $this->all_ads_options );
        $the_query->found_posts = count( $posts );

        $this->collect_filters( $posts );

        return $posts;
    }

    /**
     * Return the instance of this class.
     */
    public static function get_instance() {
        // If the single instance hasn't been set, set it now.
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * If there are expired ads, add an expired view.
     *
     * @param array $views currently available views.
     *
     * @return array
     */
    public function add_expired_view( $views ) {
        $count = $this->count_expired_ads();
        if ( empty( $count ) ) {
            return $views;
        }
        $views[ VK_Adnetwork_Ad_Expiration::POST_STATUS ] = sprintf(
            wp_kses('<a href="%s" %s>%s <span class="count">(%d)</span></a>', ['a' => ['href' => true], 'span' => ['class' => true]]),
            add_query_arg( [
                'post_type' => VK_Adnetwork::POST_TYPE_SLUG,
                'addate'    => 'vk_adnetwork-filter-expired',
                'orderby'   => 'expiry_date',
                'order'     => 'DESC',
            ], 'edit.php' ),
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            isset( $_REQUEST['addate'] ) && $_REQUEST['addate'] === 'vk_adnetwork-filter-expired' ? 'class="current" aria-current="page"' : '',
            esc_attr_x( 'Expired', 'Post list header for expired ads.', 'vk-adnetwork' ),
            absint($count)
        );

        return array_replace( array_intersect_key( $this->views_order(), $views ), $views );
    }

    /**
     * Get the number of ads that have expired.
     *
     * @return int
     */
    private function count_expired_ads() {
        return ( new WP_Query( [
            'post_type'   => VK_Adnetwork::POST_TYPE_SLUG,
            'post_status' => VK_Adnetwork_Ad_Expiration::POST_STATUS,
        ] ) )->found_posts;
    }

    /**
     * If there are ads with an expiration date in the future, add an expiring view.
     *
     * @param array $views currently available views.
     *
     * @return array
     */
    public function add_expiring_view( $views ) {
        $count = $this->count_expiring_ads();
        if ( empty( $count ) ) {
            return $views;
        }
        $views['expiring'] = sprintf(
            wp_kses('<a href="%s" %s>%s <span class="count">(%d)</span></a>', ['a' => ['href' => true], 'span' => ['class' => true]]),
            add_query_arg( [
                'post_type' => VK_Adnetwork::POST_TYPE_SLUG,
                'addate'    => 'vk_adnetwork-filter-expiring',
                'orderby'   => 'expiry_date',
                'order'     => 'ASC',
            ], 'edit.php' ),
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            isset( $_REQUEST['addate'] ) && $_REQUEST['addate'] === 'vk_adnetwork-filter-expiring' ? 'class="current" aria-current="page"' : '',
            esc_attr_x( 'Expiring', 'Post list header for ads expiring in the future.', 'vk-adnetwork' ),
            absint($count)
        );

        return array_replace( array_intersect_key( $this->views_order(), $views ), $views );
    }

    /**
     * Get the number of ads that have an expiration date in the future.
     *
     * @return int
     */
    private function count_expiring_ads() {
        return ( new WP_Query( [
            'post_type'   => VK_Adnetwork::POST_TYPE_SLUG,
            'post_status' => 'any',
            'meta_query'  => [      // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'     => VK_Adnetwork_Ad_Expiration::POST_META,
                    'value'   => current_time( 'mysql', true ),
                    'compare' => '>=',
                    'type'    => 'DATETIME',
                ],
            ],
        ] ) )->found_posts;
    }

    /**
     * Our expected order of views.
     *
     * @return string[]
     */
    private function views_order() {
        static $views_order;
        if ( $views_order === null ) {
            $views_order = array_flip( [ 'all', 'mine', 'publish', 'future', 'expiring', VK_Adnetwork_Ad_Expiration::POST_STATUS, 'draft', 'pending', 'trash' ] );
        }

        return $views_order;
    }

    /**
     * Filter by expiring or expired ads.
     *
     * @param string    $filter The current filter name, expired or expiring.
     * @param WP_Post[] $posts  The array of posts.
     *
     * @return WP_Post[]
     */
    private function filter_expired_ads( $filter, $posts ) {
        $now = time();

        return array_filter( $posts, function( WP_Post $post ) use ( $now, $filter ) {
            $option = $this->all_ads_options[ $post->ID ];
            if ( empty( $option['expiry_date'] ) ) {
                return false;
            }

            return (
                // filter by ads already expired.
                ( $filter === 'vk_adnetwork-filter-expired' && $option['expiry_date'] <= $now )
                // filter by ads expiring in the future.
                || ( $filter === 'vk_adnetwork-filter-expiring' && $option['expiry_date'] > $now )
            );
        } );
    }

    /**
     * Displays the list of views available for Ads.
     */
    public function ad_views() {
        global $wp_list_table;

        if ( ! $this->is_ad_list_screen() ) {
            return;
        }

        // unregister the hook to prevent the navigation to appear again below the footer
        remove_action( 'manage_posts_extra_tablenav', [ $this, 'ad_views' ] );

        $views = $wp_list_table->get_views();
        /**
         * Filters the list of available list table views.
         *
         * The dynamic portion of the hook name, `$this->screen->id`, refers
         * to the ID of the current screen.
         *
         * @param string[] $views An array of available list table views.
         */
        $views = apply_filters( "views_{$wp_list_table->screen->id}", $views );

        if ( empty( $views ) ) {
            return;
        }

        $wp_list_table->screen->render_screen_reader_content( 'heading_views' );
        $views_new = [];

        foreach ( $views as $class => $view ) {
            $view                = str_replace( [ ')', '(' ], [ '', '' ], $view );
            $class              .= strpos( $view, 'current' ) ? ' vk_adnetwork-button-primary' : ' vk_adnetwork-button-secondary';
            $views_new[ $class ] = $view;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $show_trash_delete_button = isset( $_GET['post_status'] ) && 'trash' === $_GET['post_status'] && have_posts() && current_user_can( get_post_type_object( $wp_list_table->screen->post_type )->cap->edit_others_posts );

        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list/view-list.php';
    }

    /**
     * Custom scripts and styles for the ad list page
     *
     * @return void
     */
    public function ad_list_scripts() {
        if ( ! $this->is_ad_list_screen() ) {
            return;
        }

        // show label before the search form if this is a search
        if ( ! empty( $_GET['s'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            wp_add_inline_style( VK_ADNETWORK_SLUG . '-admin-styles', "
                .post-type-vk_adnetwork .search-box:before { content: '" . esc_html__( 'Showing search results for', 'vk-adnetwork' ) . "'; float: left; margin-right: 8px; line-height: 30px; font-weight: 700; }
                .post-type-vk_adnetwork .subtitle { display: none; }
            " );
        }

        // adjust search form when there are no results
        if ( self::uses_filter_or_search() && 0 === count( $this->all_ads ) ) {
            wp_add_inline_style( VK_ADNETWORK_SLUG . '-admin-styles', '.post-type-vk_adnetwork .search-box { display: block; margin-top: 10px; }' );
            return;
        }

        // show filters, if the option to show them is enabled or a search is running
        if ( get_current_screen()->get_option( 'show-filters' ) || self::uses_filter_or_search() ) {
            wp_add_inline_style( VK_ADNETWORK_SLUG . '-admin-styles', '.post-type-vk_adnetwork .search-box { display: block; }
                .post-type-vk_adnetwork .tablenav.top .alignleft.actions:not(.bulkactions) { display: block; }' );
            return;
        }

        wp_add_inline_script( VK_ADNETWORK_SLUG . '-admin-script', "
            jQuery( document ).ready( function ( $ ) {
                $( '#vk_adnetwork-show-filters' ).on( 'click', function() {
                    const disabled = $( this ).find( '.dashicons' ).hasClass('dashicons-arrow-up');
                    $( '.post-type-vk_adnetwork .search-box' ).toggle(!disabled);
                    $( '.post-type-vk_adnetwork .tablenav.top .alignleft.actions:not(.bulkactions)' ).toggle(!disabled);
                    $( '#vk_adnetwork-show-filters .dashicons' ).toggleClass( 'dashicons-filter', disabled );
                    $( '#vk_adnetwork-show-filters .dashicons' ).toggleClass( 'dashicons-arrow-up', ! disabled );
                });
            });
        " );
    }

    /**
     * If there is an addate dimension, add it to the filter.
     * This ensures that the "view" buttons for expiring and expired ads
     * maintain the `.current` class.
     *
     * @return void
     */
    public function send_addate_in_filter() {
        if (
            ! isset( $_GET['post_type'] )                           // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            || $_GET['post_type'] !== VK_Adnetwork::POST_TYPE_SLUG  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            || empty( $_GET['addate'] )                             // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ) {
            return;
        }
        printf(
            wp_kses('<input type="hidden" name="addate" value="%s">',
                ['input' => ['type' => true, 'name' => true, 'value' => true]]
            ),
            esc_attr(sanitize_text_field($_GET['addate']))          // phpcs:ignore WordPress.Security.NonceVerification.Recommended
       );

    }
}
