<?php

/**
 * Class VK_Adnetwork_Frontend_Checks
 *
 * Handle Ad Health and other notifications and checks in the frontend.
 */
class VK_Adnetwork_Frontend_Checks {
    /**
     * True if 'the_content' was invoked, false otherwise.
     *
     * @var bool
     */
    private $did_the_content = false;
    private $has_many_the_content = false;

    /**
     * Constructor.
     */
    public function __construct() {
        // Wait until other plugins (for example Elementor) have disabled admin bar using `show_admin_bar` filter.
        add_action( 'template_redirect', [ $this, 'init' ], 11 );

        if ( wp_doing_ajax() ) {
            add_filter( 'vk-adnetwork-ad-output', [ $this, 'after_ad_output' ], 10, 2 );
        }
    }

    /**
     * Ad Health init.
     */
    public function init() {
        if ( ! is_admin()
            && is_admin_bar_showing()
            && current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) )
            && VK_Adnetwork_Ad_Health_Notices::notices_enabled()
        ) {
            add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu' ], 1000 );
            add_filter( 'the_content', [ $this, 'set_did_the_content' ] );
            add_action( 'wp_footer', [ $this, 'footer_checks' ], -101 );
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
            add_filter( 'vk-adnetwork-ad-select-args', [ $this, 'ad_select_args_callback' ] );
            add_filter( 'vk-adnetwork-ad-output', [ $this, 'after_ad_output' ], 10, 2 );
        }

        if ( VK_Adnetwork_Ad_Health_Notices::notices_enabled() ) {
            add_action( 'body_class', [ $this, 'body_class' ] );
        }
    }

    /**
     * Notify ads loaded with AJAX.
     *
     * @param array $args ad arguments.
     * @return array $args
     */
    public function ad_select_args_callback( $args ) {
        $args['frontend-check'] = true;
        return $args;
    }

    /**
     * Enqueue scripts
     * needs to add ajaxurl in case no other plugin is doing that
     */
    public function enqueue_scripts() {
        if ( vk_adnetwork_is_amp() ) {
            return;
        }

        // we don’t have our own script, so we attach this information to jquery
        wp_localize_script( 'jquery', 'vk_adnetwork_frontend_checks',
            [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
    }

    /**
     * List current ad situation on the page in the admin-bar.
     *
     * @param object $wp_admin_bar WP_Admin_Bar.
     */
    public function add_admin_bar_menu( $wp_admin_bar ) {
        global $wp_the_query, $post, $wp_scripts;

        $options = VK_Adnetwork_Plugin::get_instance()->options();

        // check if current user was identified as a bot.
        if( VK_Adnetwork::get_instance()->is_bot() ) {
            $nodes[] = [ 'type' => 1, 'data' => [
                'parent' => 'vk_adnetwork_ad_health',
                'id'     => 'vk_adnetwork_user_is_bot',
                'title'  => esc_html__( 'You look like a bot', 'vk-adnetwork' ),
                'href'   => esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ad-health' ) ), // #look-like-bot'
                'meta'   => [
                    'class'  => 'vk_adnetwork_ad_health_warning',
                    'target' => '_blank'
                ]
            ] ];
        }

        // check if an ad blocker is enabled    // проверка, включен ли блокировщик рекламы
        // Hidden, will be shown using js.      // Скрыто, будет отображаться с помощью js.
        $nodes[] = [ 'type' => 2, 'data' => [
            'parent' => 'vk_adnetwork_ad_health',
            'id'     => 'vk_adnetwork_ad_health_adblocker_enabled',
            'title'  => esc_html__( 'Ad blocker enabled', 'vk-adnetwork' ),
            'meta'   => [
                'class'  => 'hidden vk_adnetwork_ad_health_warning',
                'target' => '_blank'
            ]
        ] ];

        if ( $wp_the_query->is_singular() ) {
            if ( $this->has_the_content_placements() ) {
                $nodes[] = [ 'type' => 2, 'data' => [
                    'parent' => 'vk_adnetwork_ad_health',
                    'id'     => 'vk_adnetwork_ad_health_the_content_not_invoked',
                    // translators: %s is the_content filter
                    'title'  => wp_kses(sprintf( __( '<em>%s</em> filter does not exist', 'vk-adnetwork' ), 'the_content' ), ['em' => true]),
                    'href'   => esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ads-not-showing-up' ) ),
                    'meta'   => [
                        'class'  => 'hidden vk_adnetwork_ad_health_warning',
                        'target' => '_blank'
                    ]
                ] ];
            }

            if ( ! empty( $post->ID ) ) {
                $ad_settings = get_post_meta( $post->ID, '_vk_adnetwork_ad_settings', true );

                if ( ! empty( $ad_settings['disable_the_content'] ) ) {
                    $nodes[] = [ 'type' => 1, 'data' => [
                        'parent' => 'vk_adnetwork_ad_health',
                        'id'     => 'vk_adnetwork_ad_health_disabled_in_content',
                        'title'  => esc_html__( 'Ads are disabled in the content of this page', 'vk-adnetwork' ),
                        'href'   => get_edit_post_link( $post->ID ) . '#vk_adnetwork-ad-settings',
                        'meta'   => [
                            'class' => 'vk_adnetwork_ad_health_warning',
                            'target' => '_blank'
                        ]
                    ] ];
                }
            } else {
                $nodes[] = [ 'type' => 1, 'data' => [
                    'parent' => 'vk_adnetwork_ad_health',
                    'id'     => 'vk_adnetwork_ad_health_post_zero',
                    'title'  => esc_html__( 'the current post ID is 0 ', 'vk-adnetwork' ),
                    'href'   => esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ad-health' ) ), //#post-id-0',
                    'meta'   => [
                        'class'  => 'vk_adnetwork_ad_health_warning',
                        'target' => '_blank'
                    ]
                ] ];
            }
        }

        $disabled_reason = VK_Adnetwork::get_instance()->disabled_reason;
        $disabled_id = VK_Adnetwork::get_instance()->disabled_id;

        if ( 'page' === $disabled_reason && $disabled_id ) {
            $nodes[] = [
                'type' => 1,
                'data' => [
                    'parent' => 'vk_adnetwork_ad_health',
                    'id'     => 'vk_adnetwork_ad_health_disabled_on_page',
                    'title'  => esc_html__( 'Ads are disabled on this page', 'vk-adnetwork' ),
                    'href'   => get_edit_post_link( $disabled_id ) . '#vk_adnetwork-ad-settings',
                    'meta'   => [
                        'class'  => 'vk_adnetwork_ad_health_warning',
                        'target' => '_blank',
                    ],
                ],
            ];
        }

        if ( 'all' === $disabled_reason ) {
            $nodes[] = [ 'type' => 1, 'data' => [
                'parent' => 'vk_adnetwork_ad_health',
                'id'     => 'vk_adnetwork_ad_health_no_all',
                'title'  => esc_html__( 'Ads are disabled on all pages', 'vk-adnetwork' ),
                'href'   => admin_url( 'admin.php?page=vk-adnetwork-settings' ),
                'meta'   => [
                    'class'  => 'vk_adnetwork_ad_health_warning',
                    'target' => '_blank'
                ]
            ] ];
        }

        if ( '404' === $disabled_reason ) {
            $nodes[] = [
                'type' => 1,
                'data' => [
                    'parent' => 'vk_adnetwork_ad_health',
                    'id'     => 'vk_adnetwork_ad_health_no_404',
                    'title'  => esc_html__( 'Ads are disabled on 404 pages', 'vk-adnetwork' ),
                    'href'   => admin_url( 'admin.php?page=vk-adnetwork-settings' ),
                    'meta'   => [
                        'class'  => 'vk_adnetwork_ad_health_warning',
                        'target' => '_blank',
                    ],
                ],
            ];
        }

        if ( 'archive' === $disabled_reason ) {
            $nodes[] = [ 'type' => 1, 'data' => [
                'parent' => 'vk_adnetwork_ad_health',
                'id'     => 'vk_adnetwork_ad_health_no_archive',
                'title'  => esc_html__( 'Ads are disabled on non singular pages', 'vk-adnetwork' ),
                'href'   => admin_url( 'admin.php?page=vk-adnetwork-settings' ),
                'meta'   => [
                    'class'  => 'vk_adnetwork_ad_health_warning',
                    'target' => '_blank'
                ]
            ] ];
        }

        $nodes[] = [ 'type' => 2, 'data' => [
            'parent' => 'vk_adnetwork_ad_health',
            'id'     => 'vk_adnetwork_ad_health_has_http',
            'title'  => sprintf( '%s %s',
                esc_html__( 'Your website is using HTTPS, but the ad code contains HTTP and might not work.', 'vk-adnetwork' ),
                // translators: %s -- empty space for ads IDs
                sprintf( esc_html__( 'Ad IDs: %s', 'vk-adnetwork'  ), '<i></i>' )
            ),
            'href'   => esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ad-health' ) ), //#https-ads',
            'meta'   => [
                'class'  => 'hidden vk_adnetwork_ad_health_warning vk_adnetwork_ad_health_has_http',
                'target' => '_blank'
            ]
        ] ];

        $nodes[] = [ 'type' => 2, 'data' => [
            'parent' => 'vk_adnetwork_ad_health',
            'id'     => 'vk_adnetwork_ad_health_incorrect_head',
            // translators: %s -- empty space for the number of ads
            'title'  => sprintf( esc_html__( 'Visible ads should not use the Header placement: %s', 'vk-adnetwork' ), '<i></i>' ),
            'href'   => esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ad-health' ) ), //#header-ads',
            'meta'   => [
                'class'  => 'hidden vk_adnetwork_ad_health_warning vk_adnetwork_ad_health_incorrect_head',
                'target' => '_blank'
            ]
        ] ];

        // link to highlight ads and jump from one ad to the next.
        // ссылка для выделения объявлений и перехода от одного объявления к другому.
        $nodes[] = [ 'type' => 3, 'amp' => false, 'data' => [
            'parent' => 'vk_adnetwork_ad_health',
            'id'     => 'vk_adnetwork_ad_health_highlight_ads',
            'title'  => esc_html__( 'highlight ads', 'vk-adnetwork' ),
            'meta'   => [
                'class' => 'vk_adnetwork_ad_health_highlight_ads',
            ],
        ] ];

        /**
         * Add new node.
         *
         * @param array $node An array that contains:
         *      'type' => 1 - warning, 2 - hidden warning that will be shown using JS, 3 - info message
         *      'data': @see WP_Admin_Bar->add_node
         * @param object  $wp_admin_bar
         */
        $nodes = apply_filters( 'vk-adnetwork-ad-health-nodes', $nodes );

        usort( $nodes, [ $this, 'sort_nodes' ] );

        // load number of already detected notices.
        $notices = VK_Adnetwork_Ad_Health_Notices::get_number_of_notices();

        if ( ! vk_adnetwork_is_amp() ) {
            $warnings = 0; // Will be updated using JS.
        } else {
            $warnings = $this->count_visible_warnings( $nodes, [ 1 ] );
        }

        $issues = $warnings;

        $this->add_header_nodes( $wp_admin_bar, $issues, $notices );

        foreach ( $nodes as $node ) {
            if ( isset( $node['data'] ) ) {
                $wp_admin_bar->add_node( $node['data'] );
            }
        }

        $this->add_footer_nodes( $wp_admin_bar, $issues );
    }


    /**
     * Add classes to the `body` tag.
     *
     * @param string[] $classes Array of existing class names.
     * @return string[] $classes Array of existing and new class names.
     */
    public function body_class( $classes ) {
        $aa_classes = [
            'aa-prefix-' . VK_Adnetwork_Plugin::get_instance()->get_frontend_prefix(),
        ];

        $disabled_reason = VK_Adnetwork::get_instance()->disabled_reason;
        if ( $disabled_reason ) {
            $aa_classes[] = 'aa-disabled-' . esc_attr( $disabled_reason );
        }

        global $post;
        if ( ! empty( $post->ID ) ) {
            $ad_settings = get_post_meta( $post->ID, '_vk_adnetwork_ad_settings', true );
            if ( ! empty( $ad_settings['disable_the_content'] ) ) {
                $aa_classes[] = 'aa-disabled-content';
            }
        }

        // hide-ads-from-bots option is enabled
        $options = VK_Adnetwork_Plugin::get_instance()->options();
        if ( ! empty( $options['block-bots'] ) ) {
            $aa_classes[] = 'aa-disabled-bots';
        }

        $aa_classes = apply_filters( 'vk-adnetwork-body-classes', $aa_classes );

        if ( ! is_array( $classes ) ) {
            $classes = [];
        }
        if ( ! is_array( $aa_classes ) ) {
            $aa_classes = [];
        }

        return array_merge( $classes, $aa_classes );
    }




    /**
     * Count visible notices and warnings.
     *
     * @param array $nodes Nodes to add.
     * @param array $types Warning types.
     */
    private function count_visible_warnings( $nodes, $types = [] ) {
        $warnings = 0;
        foreach ( $nodes as $node ) {
            if ( ! isset( $node['type'] ) || ! isset( $node['data'] ) ) { continue; }
            if ( in_array( $node['type'], $types ) ) {
                $warnings++;
            }
        }
        return $warnings;
    }

    /**
     * Add header nodes.
     *
     * @param object $wp_admin_bar WP_Admin_Bar object.
     * @param int    $issues Number of all issues.
     * @param int    $notices Number of notices.
     */
    private function add_header_nodes( $wp_admin_bar, $issues, $notices ) {
        $wp_admin_bar->add_node( [
            'id'       => 'vk_adnetwork_ad_health',
            'title'    => esc_html__( 'Ad Health', 'vk-adnetwork' ) . '&nbsp;<span class="vk-adnetwork-issue-counter">' . $issues . '</span>',
            'parent'   => false,
            'href'     => admin_url( 'admin.php?page=vk-adnetwork' ),
            'meta'     => [
                'class' => $issues ? 'vk_adnetwork-adminbar-is-warnings': '',
            ],
        ] );


        // show that there are backend notices
        if ( $notices ) {
            $wp_admin_bar->add_node( [
                'parent' => 'vk_adnetwork_ad_health',
                'id'     => 'vk_adnetwork_ad_health_more',
                // translators: %s is the number of notices
                'title'  => sprintf( esc_html__( 'Show %d more notifications', 'vk-adnetwork' ), absint( $notices ) ),
                'href'   => admin_url( 'admin.php?page=vk-adnetwork' ),
            ] );
        }
    }

    /**
     * Add footer nodes.
     *
     * @return object $wp_admin_bar WP_Admin_Bar object.
     * @param int $issues Number of all issues.
     */
    private function add_footer_nodes( $wp_admin_bar, $issues ) {
        if ( ! $issues ) {
            $wp_admin_bar->add_node( [
                'parent' => 'vk_adnetwork_ad_health',
                'id'     => 'vk_adnetwork_ad_health_fine',
                'title'  => esc_html__( 'Everything is fine', 'vk-adnetwork' ),
                'href'   => false,
                'meta'   => [
                    'target' => '_blank',
                ]
            ] );
        }

        $wp_admin_bar->add_node( [
            'parent' => 'vk_adnetwork_ad_health',
            'id'     => 'vk_adnetwork_ad_health_support',
            'title'  => esc_html__( 'Get help', 'vk-adnetwork' ),
            'href'   => VK_Adnetwork_Plugin::support_url(),
            'meta'   => [
                'target' => '_blank',
            ]
        ] );
    }

    /**
     * Sort nodes.
     */
    function sort_nodes( $a, $b ) {
        if ( ! isset( $a['type'] ) || ! isset( $b['type'] ) ) {
            return 0;
        }
        if ( $a['type'] == $b['type'] ) {
            return 0;
        }
        return ( $a['type'] < $b['type'] ) ? -1 : 1;
    }

    /**
     * Set variable to 'true' when 'the_content' filter is invoked.
     *
     * @param string $content
     * @return string $content
     */
    public function set_did_the_content( $content ) {
        if ( ! $this->did_the_content ) {
            $this->did_the_content = true;
        }

        if ( VK_Adnetwork::get_instance()->has_many_the_content() ) {
            $this->has_many_the_content = true;
        }
        return $content;
    }

    /**
     * Check conditions and display warning.
     * Conditions:
     *  AdBlocker enabled,
     *  jQuery is included in header
     */
    public function footer_checks() {
        ob_start();
        ?><!-- VK AdNetwork: <?php esc_html_e( 'the following code is used for automatic error detection and only visible to admins', 'vk-adnetwork' ); ?>-->
        <style>#wp-admin-bar-vk_adnetwork_ad_health .hidden { display: none; }
        #wp-admin-bar-vk_adnetwork_ad_health-default a:after { content: "\25BA"; margin-left: .5em; font-size: smaller; }
        #wp-admin-bar-vk_adnetwork_ad_health-default .vk_adnetwork_ad_health_highlight_ads div:before { content: "\f177"; margin-right: .2em; line-height: 1em; padding: 0.2em 0 0; color: inherit; }
        #wp-admin-bar-vk_adnetwork_ad_health-default .vk_adnetwork_ad_health_highlight_ads div:hover { color: #00b9eb; cursor: pointer; }
        #wpadminbar .vk-adnetwork-issue-counter { background-color: #d54e21; display: none; padding: 1px 7px 1px 6px!important; border-radius: 50%; color: #fff; }
        #wpadminbar .vk_adnetwork-adminbar-is-warnings .vk-adnetwork-issue-counter { display: inline; }
        .vk-adnetwork-highlight-ads { outline:4px solid #0474A2 !important; }

        .vk_adnetwork-frontend-notice { display: none; position: fixed; top: 0; z-index: 1000; left: 50%; max-width: 500px; margin-left: -250px; padding: 30px 10px 10px 10px; border: 0 solid #0074a2; border-top: 0; border-radius: 0 0 5px 5px; box-shadow: 0 0 15px rgba(0,0,0,0.3); background: #ffffff; background: rgba(255,255,255,0.95); font-size: 16px; font-family: Arial, Verdana, sans-serif; line-height: 1.5em; color: #444444; }

        .vk_adnetwork-frontend-notice a, .vk_adnetwork-frontend-notice a:link { color: #0074a2; text-decoration: none; }
        .vk_adnetwork-frontend-notice ul { }
        .vk_adnetwork-frontend-notice ul li { line-height: 1.5em; }

        .vk_adnetwork-frontend-notice .vk_adnetwork-close-notice { position: absolute; top: 5px; right: 0; display: block; font-size: 20px; width: 30px; height: 30px; line-height: 30px; text-decoration: none; text-align: center; font-weight: bold; color: #444444; cursor: pointer; }
        .vk_adnetwork-frontend-notice .vk_adnetwork-notice-var1 { font-size: 14px; font-style: italic; text-align: center; }
        .vk_adnetwork-frontend-notice .vk_adnetwork-frontend-notice-choice { text-align: center; }
        .vk_adnetwork-frontend-notice .vk_adnetwork-frontend-notice-choice:after { display: block; content: " "; clear: both; }

        @media screen and (max-width: 510px) {
             .vk_adnetwork-frontend-notice { left: 0; width: 100%; margin-left: 0; }
        }
        </style>
        <?php echo wp_kses(ob_get_clean(), ['style' => true]);

        if ( vk_adnetwork_is_amp() ) {
            return;
        }

        wp_enqueue_script( VK_ADNETWORK_SLUG . '-footer-checks-script', esc_url(VK_ADNETWORK_BASE_URL . 'admin/assets/js/advertisement.js'), [], VK_ADNETWORK_VERSION, ['in_footer'  => true]);
        ob_start();
        ?>
        var vk_adnetwork_frontend_checks = {
            showCount: function() {
                try {
                    // Count only warnings that have the 'vk_adnetwork_ad_health_warning' class.
                    var warning_count = document.querySelectorAll( '.vk_adnetwork_ad_health_warning:not(.hidden)' ).length;
                    var fine_item = document.getElementById( 'wp-admin-bar-vk_adnetwork_ad_health_fine' );
                } catch ( e ) { return; }

                var header = document.querySelector( '#wp-admin-bar-vk_adnetwork_ad_health > a' );
                if ( warning_count ) {
                    if ( fine_item ) {
                        // Hide 'fine' item.
                        fine_item.className += ' hidden';
                    }

                    if ( header ) {
                        header.innerHTML = header.innerHTML.replace(RegExp('<span class="vk-adnetwork-issue-counter">\\d*</span>'), '') + '<span class="vk-adnetwork-issue-counter">' + warning_count + '</span>';
                        // add class
                        header.className += ' vk_adnetwork-adminbar-is-warnings';
                    }
                } else {
                    // Show 'fine' item.
                    if ( fine_item ) {
                        fine_item.classList.remove('hidden');
                    }

                    // Remove counter.
                    if ( header ) {
                        header.innerHTML = header.innerHTML.replace(RegExp('<span class="vk-adnetwork-issue-counter">\\d*</span>'), '');
                        header.classList.remove('vk_adnetwork-adminbar-is-warnings');
                    }
                }
            },

            array_unique: function( array ) {
                var r= [];
                for ( var i = 0; i < array.length; i++ ) {
                    if ( r.indexOf( array[ i ] ) === -1 ) {
                        r.push( array[ i ] );
                    }
                }
                return r;
            },

            /**
             * Add item to Ad Health node.
             *
             * @param string selector Selector of the node.
             * @param string/array item item(s) to add.
             */
            add_item_to_node: function( selector, item ) {
                if ( typeof item === 'string' ) {
                    item = item.split();
                }
                var selector = document.querySelector( selector );
                if ( selector ) {
                    selector.className = selector.className.replace( 'hidden', '' );
                    selector.innerHTML = selector.innerHTML.replace( RegExp('(<i>)(.*?)(</i>)'), function( match, p1, p2, p3 ) {
                        p2 = ( p2 ) ? p2.split( ', ' ) : [];
                        p2 = p2.concat( item );
                        p2 = vk_adnetwork_frontend_checks.array_unique( p2 );
                        return p1 + p2.join( ', ' ) + p3;
                    } );
                    vk_adnetwork_frontend_checks.showCount();
                }
            },

            /**
             * Add item to Ad Health notices in the backend
             *
             * @param key of the notice
             * @param attr
             * @returns {undefined}
             */
            add_item_to_notices: function( key, attr = '' ) {
                var cookie = vk_adnetwork.get_cookie( 'vk_adnetwork_ad_health_notices' );
                if( cookie ){
                    vk_adnetwork_cookie_notices = JSON.parse( cookie );
                } else {
                    vk_adnetwork_cookie_notices = new Array();
                }
                // stop if notice was added less than 1 hour ago
                if( 0 <= vk_adnetwork_cookie_notices.indexOf( key ) ){
                    return;
                }
                var query = {
                    action: 'vk_adnetwork-ad-health-notice-push',
                    key: key,
                    attr: attr,
                    nonce: '<?php echo esc_html(wp_create_nonce('vk-adnetwork-ad-health-ajax-nonce')); ?>'
                };
                // send query
                // update notices and cookie
                jQuery.post( vk_adnetwork_frontend_checks.ajax_url, query, function (r) {
                    vk_adnetwork_cookie_notices.push( key );
                    var notices_str = JSON.stringify( vk_adnetwork_cookie_notices );
                    vk_adnetwork.set_cookie_sec( 'vk_adnetwork_ad_health_notices', notices_str, 3600 ); // 1 hour
                });
            },
            /**
             * Update status of frontend notices
             *
             * @param key of the notice
             * @param attr
             * @returns {undefined}
             */
            update_frontend_notices: function( key, attr = '' ) {
                var query = {
                    action: 'vk_adnetwork-ad-frontend-notice-update',
                    key: key,
                    attr: attr,
                    nonce: '<?php echo esc_html(wp_create_nonce('vk-adnetwork-frontend-notice-nonce')); ?>'
                };
                // send query
                jQuery.post( vk_adnetwork_frontend_checks.ajax_url, query, function (r) {})
                // close message when done.
                .done(function() {
                    content_obj.slideUp();
                });
            }

        };

        (function(d, w) {
                // var not_head_jQuery = typeof jQuery === 'undefined';

                var addEvent = function( obj, type, fn ) {
                    if ( obj.addEventListener )
                        obj.addEventListener( type, fn, false );
                    else if ( obj.attachEvent )
                        obj.attachEvent( 'on' + type, function() { return fn.call( obj, window.event ); } );
                };

                // highlight ads that use VK AdNetwork placements
                function highlight_ads() {
                    /**
                     * Selectors:
                     * Placement container: div[id^="<?php echo esc_attr(VK_Adnetwork_Plugin::get_instance()->get_frontend_prefix());?>"]
                     */
                    try {
                        var ad_wrappers = document.querySelectorAll('div[id^="<?php echo esc_attr(VK_Adnetwork_Plugin::get_instance()->get_frontend_prefix());?>"]')
                    } catch ( e ) { return; }
                    for ( i = 0; i < ad_wrappers.length; i++ ) {
                            ad_wrappers[i].title = ad_wrappers[i].className;
                            ad_wrappers[i].className += ' vk-adnetwork-highlight-ads';
                            // in case we want to remove it later
                            // ad_wrappers[i].className = ad_wrappers[i].className.replace( 'vk-adnetwork-highlight-ads', '' );
                    }
                }

                vk_adnetwork_ready( function() {
                    var adblock_item = d.getElementById( 'wp-admin-bar-vk_adnetwork_ad_health_adblocker_enabled' );
                    // jQuery_item = d.getElementById( 'wp-admin-bar-vk_adnetwork_ad_health_jquery' ),

                    // handle click on the highlight_ads link
                    var highlight_link = d.getElementById( 'wp-admin-bar-vk_adnetwork_ad_health_highlight_ads' );
                    addEvent( highlight_link, 'click', highlight_ads );

                    if ( adblock_item && typeof vk_adnetwork_adblocker_test === 'undefined' ) {
                        // show hidden item
                        adblock_item.className = adblock_item.className.replace( /hidden/, '' );
                    }

                    /* if ( jQuery_item && not_head_jQuery ) {
                        // show hidden item
                        jQuery_item.className = jQuery_item.className.replace( /hidden/, '' );
                    }*/

                    <?php if ( ! $this->did_the_content ) : ?>
                        var the_content_item = d.getElementById( 'wp-admin-bar-vk_adnetwork_ad_health_the_content_not_invoked' );
                        if ( the_content_item ) {
                            the_content_item.className = the_content_item.className.replace( /hidden/, '' );
                        }
                    <?php endif; ?>

                    vk_adnetwork_frontend_checks.showCount();
                });


        })(document, window);
        <?php
        wp_add_inline_script(VK_ADNETWORK_SLUG . '-footer-checks-script', ob_get_clean());
    }

    /**
     * Inject JS after ad content.
     *
     * @param string $content ad content
     * @param VK_Adnetwork_Ad $ad
     * @return mixed|string $content ad content
     */
    public function after_ad_output( $content, VK_Adnetwork_Ad $ad ) {
        if ( ! isset( $ad->args['frontend-check'] ) ) { return $content; }

        if ( vk_adnetwork_is_amp() ) {
            return $content;
        }

        if ( VK_Adnetwork_Ad_Debug::is_https_and_http( $ad ) ) {
            ob_start(); ?>
            <script>vk_adnetwork_ready( function() {
                var ad_id = '<?php echo esc_attr($ad->id); ?>';
                vk_adnetwork_frontend_checks.add_item_to_node( '.vk_adnetwork_ad_health_has_http', ad_id );
                vk_adnetwork_frontend_checks.add_item_to_notices( 'ad_has_http', { append_key: ad_id, ad_id: ad_id } );
            });</script>
            <?php
            $content .= VK_Adnetwork_Utils::vk_adnetwork_get_inline_asset( ob_get_clean() );
        }

        if ( ! VK_Adnetwork_Frontend_Checks::can_use_head_placement( $content, $ad ) ) {
            ob_start(); ?>
            <script>vk_adnetwork_ready( function() {
            var ad_id = '<?php echo esc_attr($ad->id); ?>';
            vk_adnetwork_frontend_checks.add_item_to_node( '.vk_adnetwork_ad_health_incorrect_head', ad_id );
            });</script>
            <?php
            $content .= VK_Adnetwork_Utils::vk_adnetwork_get_inline_asset( ob_get_clean() );
        }



        return $content;
    }


    /**
     * Check if the 'Header Code' placement can be used to delived the ad.
     *
     * @param string          $content Ad content.
     * @param VK_Adnetwork_Ad $ad VK_Adnetwork_Ad.
     * @return bool
     */
    public static function can_use_head_placement( $content, VK_Adnetwork_Ad $ad ) {

        if ( ! $ad->is_head_placement ) {
            return true;
        }

        // strip linebreaks, because, a line break after a comment is identified as a text node.
        $content = preg_replace( "/[\r\n]/", "", $content );

        if ( ! $dom = self::get_ad_dom( $content ) ) {
            return true;
        }

        $body = $dom->getElementsByTagName( 'body' )->item( 0 );

        $count = $body->childNodes->length;
        for ( $i = 0; $i < $count; $i++ ) {
            $node = $body->childNodes->item( $i );

            if ( XML_TEXT_NODE  === $node->nodeType ) {
                return false;
            }

            if ( XML_ELEMENT_NODE === $node->nodeType
                && ! in_array( $node->nodeName, [ 'meta', 'link', 'title', 'style', 'script', 'noscript', 'base' ] ) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Convert ad content to a DOMDocument.
     *
     * @param string $content
     * @return DOMDocument|false
     */
    private static function get_ad_dom( $content ) {
        if ( ! extension_loaded( 'dom' ) ) {
            return false;
        }
        $libxml_previous_state = libxml_use_internal_errors( true );
        $dom = new DOMDocument();
        $result = $dom->loadHTML( '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>' );

        libxml_clear_errors();
        libxml_use_internal_errors( $libxml_previous_state );

        if ( ! $result ) {
            return false;
        }

        return $dom;
    }

    /**
     * Check if at least one placement uses `the_content`.
     *
     * @return bool True/False.
     */
    private function has_the_content_placements() {
        $placements = VK_Adnetwork::get_ad_placements_array();
        $placement_types = VK_Adnetwork_Placements::get_placement_types();
        // Find a placement that depends on 'the_content' filter.
        foreach ( $placements as $placement ) {
            if ( isset ( $placement['type'] )
                && ! empty( $placement_types[ $placement['type'] ]['options']['uses_the_content'] ) ) {
                return true;
            }
        }
        return false;
    }
}
