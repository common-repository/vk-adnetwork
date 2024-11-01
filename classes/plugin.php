<?php

/**
 * WordPress integration and definitions:
 *
 * - posttypes
 * - taxonomy
 * - textdomain
 */
class VK_Adnetwork_Plugin {
    /**
     * Instance of VK_Adnetwork_Plugin
     *
     * @var object VK_Adnetwork_Plugin
     */
    protected static $instance;

    /**
     * Instance of VK_Adnetwork_Model
     *
     * @var object VK_Adnetwork_Model
     */
    protected $model;

    /**
     * Plugin options
     *
     * @var array $options
     */
    protected $options;

    /**
     * Interal plugin options – set by the plugin
     *
     * @var     array $internal_options
     */
    protected $internal_options;

    /**
     * Default prefix of selectors (id, class) in the frontend
     * can be changed by options
     *
     * @var VK_Adnetwork_Plugin
     */
    const DEFAULT_FRONTEND_PREFIX = 'vk_adnetwork-';

    /**
     * Frontend prefix for classes and IDs
     *
     * @var string $frontend_prefix
     */
    private $frontend_prefix;

    /**
     * VK_Adnetwork_Plugin constructor.
     */
    private function __construct() {
        register_activation_hook( VK_ADNETWORK_BASE, [ $this, 'activate' ] );
        register_deactivation_hook( VK_ADNETWORK_BASE, [ $this, 'deactivate' ] );
        register_uninstall_hook( VK_ADNETWORK_BASE, [ 'VK_Adnetwork_Plugin', 'uninstall' ] );

        add_action( 'plugins_loaded', [ $this, 'wp_plugins_loaded' ], 20 );
        add_action( 'init', [ $this, 'run_upgrades' ], 9 );
    }

    /**
     * Get instance of VK_Adnetwork_Plugin
     *
     * @return VK_Adnetwork_Plugin
     */
    public static function get_instance() {
        // If the single instance hasn't been set, set it now.
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get instance of VK_Adnetwork_Model
     *
     * @param VK_Adnetwork_Model $model model to access data.
     */
    public function set_model( VK_Adnetwork_Model $model ) {
        $this->model = $model;
    }

    /**
     * Execute various hooks after WordPress and all plugins are available
     */
    public function wp_plugins_loaded() {
        // Load plugin text domain.
        $this->load_plugin_textdomain();

        // activate plugin when new blog is added on multisites // -TODO this is admin-only.
        add_action( 'wpmu_new_blog', [ $this, 'activate_new_site' ] );

        // Load public-facing style sheet and JavaScript.
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'wp_head', [ $this, 'print_head_scripts' ], 7 );
        // higher priority to make sure other scripts are printed before.
        // более высокий приоритет, чтобы убедиться, что другие сценарии были напечатаны раньше.
        add_action( 'wp_enqueue_scripts', [ $this, 'print_footer_scripts' ], 100 ); // no 'wp_footer'

        // add short codes.
        add_shortcode( 'vk_adnetwork_the_ad', [ $this, 'shortcode_display_ad' ] );
        add_shortcode( 'vk_adnetwork_the_ad_placement', [ $this, 'shortcode_display_ad_placement' ] );

        // load widgets.
        add_action( 'widgets_init', [ $this, 'widget_init' ] );

        // Call action hooks for ad status changes.
        add_action( 'transition_post_status', [ $this, 'transition_ad_status' ], 10, 3 );

        // register expired post status.
        VK_Adnetwork_Ad_Expiration::register_post_status();

        // if expired ad gets untrashed, revert it to expired status (instead of draft).
        add_filter( 'wp_untrash_post_status', [ VK_Adnetwork_Ad_Expiration::class, 'wp_untrash_post_status' ], 10, 3 );

        // load display conditions.
        new VK_Adnetwork_Frontend_Checks();
        new VK_Adnetwork_Compatibility();
        VK_Adnetwork_Ad_Health_Notices::get_instance(); // load to fetch notices.
    }

    /**
     * Run upgrades.
     *
     * Compatibility with the Piklist plugin that has a function hooked to `posts_where` that access $GLOBALS['wp_query'].
     * Since `VK_Adnetwork_Upgrades` applies `posts_where`: (`VK_Adnetwork_Admin_Notices::get_instance()` >
     * `VK_Adnetwork::get_number_of_ads()` > new WP_Query > ... 'posts_where') this function is hooked to `init` so that `$GLOBALS['wp_query']` is instantiated.
     * Совместимость с плагином Piklist, у которого есть функция, подключенная к `posts_where`, которая обращается к $GLOBALS['wp_query'].
     * Поскольку `VK_Adnetwork_Upgrades` применяет `posts_where`: (`VK_Adnetwork_Admin_Notices::get_instance()` >
     * `VK_Adnetwork::get_number_of_ads()` > новый WP_Query > ... 'posts_where') эта функция подключена к `init`, так что создается экземпляр `$GLOBALS['wp_query']`.
     */
    public function run_upgrades() {
        /**
         * Run upgrades, if this is a new version or version does not exist.
         */
        $internal_options = $this->internal_options();

        if ( ! defined( 'DOING_AJAX' ) && ( ! isset( $internal_options['version'] ) || version_compare( $internal_options['version'], VK_ADNETWORK_VERSION, '<' ) ) ) {
            new VK_Adnetwork_Upgrades();
        }
    }

    /**
     * Register and enqueue public-facing style sheet.
     */
    public function enqueue_styles() {
        // wp_enqueue_style( $this->get_plugin_slug() . '-plugin-styles', plugins_url('assets/css/public.css', __FILE__), array(), VK_ADNETWORK_VERSION);
    }

    /**
     * Return the plugin slug.
     *
     * @return   string plugin slug variable.
     */
    public function get_plugin_slug() {
        return VK_ADNETWORK_SLUG;
    }

    /**
     * Register and enqueues public-facing JavaScript files.
     */
    public function enqueue_scripts() {
        if ( vk_adnetwork_is_amp() ) {
            return;
        }
        // wp_enqueue_script( $this->get_plugin_slug() . '-plugin-script', plugins_url('assets/js/public.js', __FILE__), array('jquery'), VK_ADNETWORK_VERSION);

        wp_enqueue_script('admailruadsasync', 'https://ad.mail.ru/static/ads-async.js', [], VK_ADNETWORK_VERSION, ['strategy'  => 'async']); // + async [ /includes/functions.php:126: str_replace(' src', ' async src', .. ]

        wp_register_script(
            $this->get_plugin_slug() . '-advanced-js',
            sprintf( '%spublic/assets/js/vk_adnetwork%s.js', VK_ADNETWORK_BASE_URL, defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ),
            [ 'jquery' ],
            VK_ADNETWORK_VERSION,
            false
        );

        $activated_js = apply_filters( 'vk-adnetwork-activate-advanced-js', isset( $this->options()['advanced-js'] ) );

        if ( $activated_js || ! empty( $_COOKIE['vk_adnetwork_frontend_picker'] ) ) {
            wp_enqueue_script( $this->get_plugin_slug() . '-advanced-js' );
        }

        wp_register_script(
            $this->get_plugin_slug() . '-frontend-picker',
            sprintf( '%spublic/assets/js/frontend-picker%s.js', VK_ADNETWORK_BASE_URL, defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ),
            [ 'jquery', $this->get_plugin_slug() . '-advanced-js' ],
            VK_ADNETWORK_VERSION,
            false
        );

        if ( ! empty( $_COOKIE['vk_adnetwork_frontend_picker'] ) ) {
            wp_enqueue_script( $this->get_plugin_slug() . '-frontend-picker' );
        }
    }

    /**
     * Print public-facing JavaScript in the HTML head.
     */
    public function print_head_scripts() {
        $short_url   = self::get_short_url();
        $attribution = '<!-- ' . $short_url . ' is managing ads with VK Adnetwork%1$s%2$s -->';
        $version     = ' ' . VK_ADNETWORK_VERSION;
        $plugin_url  = self::get_group_by_url( $short_url, 'a' ) ? ' – ' . VK_ADNETWORK_URL : '';

        echo wp_kses_post(apply_filters('vk-adnetwork-attribution', sprintf($attribution, $version, $plugin_url)));

        if ( vk_adnetwork_is_amp() ) {
            return;
        }

        wp_enqueue_script( $this->get_plugin_slug() . '-plugin-script', plugins_url('../public/assets/js/public.js', __FILE__), ['jquery'], VK_ADNETWORK_VERSION, ['in_footer'  => false]);

        wp_add_inline_script($this->get_plugin_slug() . '-plugin-script',
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            file_get_contents(
                sprintf(
                    '%spublic/assets/js/ready%s.js',
                    VK_ADNETWORK_BASE_PATH,
                    defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min'
                )
            )
        );
    }

    /**
     * Print inline scripts in wp_footer.
     */
    public function print_footer_scripts() {
        if ( vk_adnetwork_is_amp() ) {
            return;
        }

        wp_enqueue_script( $this->get_plugin_slug() . '-footer-script', plugins_url('../public/assets/js/footer.js', __FILE__), ['jquery'], VK_ADNETWORK_VERSION, ['in_footer'  => true]);

        wp_add_inline_script(
            $this->get_plugin_slug() . '-footer-script',
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            file_get_contents(
                sprintf(
                    '%spublic/assets/js/ready-queue%s.js',
                    VK_ADNETWORK_BASE_PATH,
                    defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min'
                )
            )
        );

/*
        wp_add_inline_script(
            $this->get_plugin_slug() . '-footer-script',
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            file_get_contents(
                sprintf(
                    '%spublic/assets/js/inpage%s.js',
                    VK_ADNETWORK_BASE_PATH,
                    defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min'
                )
            )
        );
*/

        // global $wpdb; $ads = $wpdb->get_results($wpdb->prepare(" SELECT id, post_content FROM $wpdb->posts WHERE post_type = %s AND post_status = '%s' AND post_content LIKE '%vk_adnetwork_inpage_slot_%' ", VK_Adnetwork::POST_TYPE_SLUG, 'publish'));
        $ads = VK_Adnetwork::get_instance()->get_model()->get_ads( ['post_status' => ['publish']] );
        foreach ($ads as $ad) {
            if (! preg_match('/vk_adnetwork_inpage_slot_(\d+)/', $ad->post_content, $match)) continue;
            $test_mode = (new VK_Adnetwork_Ad( $ad->ID ))->options( 'output.mtdebugmode', false ) ? 'params: { test_mode: 1 },' : '';
            // /\ т.е. в код плеера вставляем 'params: { test_mode: 1 },'
            // только если в админке прожата галочка [х] Включить режим отладки (VK AdNetwork)
            $inpage_configs[] = sprintf(
                '{ container: "#vk_adnetwork_inpage_slot_%s", slot: %s, autoStart: true, %s onError: (e) => {console.log("AdMan error:", e)}}',
                $match[1], $match[1], $test_mode
            );
        }
        if ($inpage_configs) {
            $inpage_configs = implode(",\n                        ", $inpage_configs);
            wp_add_inline_script($this->get_plugin_slug() . '-footer-script', "
                let players;
                function initAdman() {
                    const configs = [
                        $inpage_configs
                    ];
                    players = configs.map( config => { const player = new window.AdManSDK(); player.init(config); player.start(); return player } )
                }
                var g = document.createElement('script');
                g.src = 'https://ad.mail.ru/static/vk-adman.js?sdk=1';
                g.type = 'application/javascript';
                g.async = !0;
                g.onload = initAdman;
                var h = document.getElementsByTagName('script')[0];
                h.parentNode.insertBefore(g,h);
                "
            );
        }

    }

    /**
     * Register the VK AdNetwork widget
     */
    public function widget_init() {
        register_widget( 'VK_Adnetwork_Widget' );
    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @param int $blog_id ID of the new blog.
     */
    public function activate_new_site( $blog_id ) {

        if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
            return;
        }

        switch_to_blog( $blog_id );
        $this->single_activate();
        restore_current_blog();
    }

    /**
     * Fired for each blog when the plugin is activated.
     */
    protected function single_activate() {
        // $this->post_types_rewrite_flush();
        // -TODO inform modules
        $this->create_capabilities();
    }

    /**
     * Fired for each blog when the plugin is deactivated.
     */
    protected function single_deactivate() {
        // -TODO inform modules
        $this->remove_capabilities();
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain( 'vk-adnetwork', false, VK_ADNETWORK_BASE_DIR . '/languages' );
    }

    /**
     * Fired when the plugin is activated.
     *
     * @param boolean $network_wide True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public function activate( $network_wide ) {
        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            if ( $network_wide ) {
                // get all blog ids.
                global $wpdb;
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
                $original_blog_id = $wpdb->blogid;

                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    $this->single_activate();
                }

                switch_to_blog( $original_blog_id );
            } else {
                $this->single_activate();
            }
        } else {
            $this->single_activate();
        }
    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @param boolean $network_wide true if VK AdNetwork should be disabled network-wide.
     *
     * True if WPMU superadmin uses
     * "Network Deactivate" action, false if
     * WPMU is disabled or plugin is
     * deactivated on an individual blog.
     */
    public function deactivate( $network_wide ) {
        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            if ( $network_wide ) {
                // get all blog ids.
                global $wpdb;
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
                $original_blog_id = $wpdb->blogid;

                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    $this->single_deactivate();
                }

                switch_to_blog( $original_blog_id );
            } else {
                $this->single_deactivate();
            }
        } else {
            $this->single_deactivate();
        }
    }

    /**
     * Shortcode to include ad in frontend
     *
     * @param array $atts shortcode attributes.
     *
     * @return string ad content.
     */
    public function shortcode_display_ad( $atts ) {
        $atts = is_array( $atts ) ? $atts : [];
        $id   = isset( $atts['id'] ) ? (int) $atts['id'] : 0;
        // check if there is an inline attribute with or without value.
        if ( isset( $atts['inline'] ) || in_array( 'inline', $atts, true ) ) {
            $atts['inline_wrapper_element'] = true;
        }
        $atts = $this->prepare_shortcode_atts( $atts );

        // use the public available function here.
        return vk_adnetwork_get_ad( $id, $atts );
    }

    /**
     * Shortcode to display content of an ad placement in frontend
     *
     * @param array $atts shortcode attributes.
     *
     * @return string ad placement content.
     */
    public function shortcode_display_ad_placement( $atts ) {
        $atts = is_array( $atts ) ? $atts : [];
        $id   = isset( $atts['id'] ) ? (string) $atts['id'] : '';
        $atts = $this->prepare_shortcode_atts( $atts );

        // use the public available function here.
        return vk_adnetwork_get_ad_placement( $id, $atts );
    }

    /**
     * Prepare shortcode attributes.
     *
     * @param array $atts array with strings.
     *
     * @return array
     */
    private function prepare_shortcode_atts( $atts ) {
        $result = [];

        /**
         * Prepare attributes by converting strings to multi-dimensional array
         * Example: [ 'output__margin__top' => 1 ]  =>  ['output']['margin']['top'] = 1
         */
        if ( ! defined( 'VK_ADNETWORK_DISABLE_CHANGE' ) || ! VK_ADNETWORK_DISABLE_CHANGE ) {
            foreach ( $atts as $attr => $data ) {
                $levels = explode( '__', $attr );
                $last   = array_pop( $levels );

                $cur_lvl = &$result;

                foreach ( $levels as $lvl ) {
                    if ( ! isset( $cur_lvl[ $lvl ] ) ) {
                        $cur_lvl[ $lvl ] = [];
                    }

                    $cur_lvl = &$cur_lvl[ $lvl ];
                }

                $cur_lvl[ $last ] = $data;
            }

            $result = array_diff_key(
                $result,
                [
                    'id'      => false,
                    'blog_id' => false,
                    'ad_args' => false,
                ]
            );
        }

        // Ad type: 'content' and a shortcode inside.
        if ( isset( $atts['ad_args'] ) ) {
            $result = array_merge( $result, json_decode( urldecode( $atts['ad_args'] ), true ) );

        }

        return $result;
    }

    /**
     * Return plugin options
     * these are the options updated by the user
     *
     * @return array $options
     */
    public function options() {
        // we can’t store options if WPML String Translations is enabled, or it would not translate the "Ad Label" option.
        if ( ! isset( $this->options ) || class_exists( 'WPML_ST_String' ) ) {
            $this->options = get_option( VK_ADNETWORK_SLUG, [] );
        }

        // allow to change options dynamically
        $this->options = apply_filters( 'vk-adnetwork-options', $this->options );

        return $this->options;
    }

    /**
     * Update plugin options (not for settings page, but if automatic options are needed)
     * @param array $options new options.
     */
    public function update_options( array $options ) {
        // do not allow to clear options.
        if ( [] === $options ) {
            return;
        }

        $this->options = $options;
        update_option( VK_ADNETWORK_SLUG, $options );
    }

    /**
     * Return internal plugin options
     * these are options set by the plugin
     *
     * @return array $options
     */
    public function internal_options() {
        if ( ! isset( $this->internal_options ) ) {
            $defaults               = [
                'version'   => VK_ADNETWORK_VERSION,
                'installed' => time(), // when was this installed.
            ];
            $this->internal_options = get_option( VK_ADNETWORK_SLUG . '-internal', [] );

            // save defaults.
            if ( [] === $this->internal_options ) {
                $this->internal_options = $defaults;
                $this->update_internal_options( $this->internal_options );

                self::get_instance()->create_capabilities();
            }

            // for versions installed prior to 1.5.3 set installed date for now.
            if ( ! isset( $this->internal_options['installed'] ) ) {
                $this->internal_options['installed'] = time();
                $this->update_internal_options( $this->internal_options );
            }
        }

        return $this->internal_options;
    }

    /**
     * Update internal plugin options
     *
     * @param array $options new internal options.
     */
    public function update_internal_options( array $options ) {
        // do not allow to clear options.
        if ( [] === $options ) {
            return;
        }

        $this->internal_options = $options;
        update_option( VK_ADNETWORK_SLUG . '-internal', $options );
    }

    /**
     * Get prefix used for frontend elements
     *
     * @return string
     */
    public function get_frontend_prefix() {
        if ( isset( $this->frontend_prefix ) ) {
            return $this->frontend_prefix;
        }

        $options = $this->options();

        $frontend_prefix = $options['front-prefix']
            ?? $options['id-prefix']
            ?? self::DEFAULT_FRONTEND_PREFIX .
                (preg_match('/[A-Za-z][A-Za-z0-9_]{4}/', wp_parse_url(get_home_url(), PHP_URL_HOST), $result) ? $result[0] . '-' : '');
        /**
         * Applying the filter here makes sure that it is the same frontend prefix for all
         * calls on this page impression
         *
         * @param string $frontend_prefix
         */
        $this->frontend_prefix = (string) apply_filters( 'vk-adnetwork-frontend-prefix', $frontend_prefix );
        $this->frontend_prefix = $this->sanitize_frontend_prefix( $frontend_prefix );

        return $this->frontend_prefix;
    }

    /**
     * Sanitize the frontend prefix to result in valid HTML classes.
     * See https://www.w3.org/TR/selectors-3/#grammar for valid tokens.
     *
     * @param string $prefix The HTML class to sanitize.
     * @param string $fallback The fallback if the class is invalid.
     *
     * @return string
     */
    public function sanitize_frontend_prefix( $prefix, $fallback = '' ) {
        $prefix   = sanitize_html_class( $prefix );
        $nonascii = '[^\0-\177]';
        $unicode  = '\\[0-9a-f]{1,6}(\r\n|[ \n\r\t\f])?';
        $escape   = sprintf( '%s|\\[^\n\r\f0-9a-f]', $unicode );
        $nmstart  = sprintf( '[_a-z]|%s|%s', $nonascii, $escape );
        $nmchar   = sprintf( '[_a-z0-9-]|%s|%s', $nonascii, $escape );

        if ( ! preg_match( sprintf( '/-?(?:%s)(?:%s)*/i', $nmstart, $nmchar ), $prefix, $matches ) ) {
            return $fallback;
        }

        return $matches[0];
    }

    /**
     * Get priority used for injection inside content
     */
    public function get_content_injection_priority() {
        $options = $this->options();

        return isset( $options['content-injection-priority'] ) ? (int) $options['content-injection-priority'] : 100;
    }

    /**
     * Returns the capability needed to perform an action
     *
     * @param string $capability a capability to check, can be internal to VK AdNetwork.
     *
     * @return string $capability a valid WordPress capability.
     */
    public static function user_cap( $capability = 'manage_options' ) {

        global $vk_adnetwork_capabilities;

        // admins can do everything.
        // is also a fallback if no option or more specific capability is given.
        if ( current_user_can( 'manage_options' ) ) {
            return 'manage_options';
        }

        return apply_filters( 'vk-adnetwork-capability', $capability );
    }

    /**
     * Create roles and capabilities
     */
    public function create_capabilities() {
        if ( $role = get_role( 'administrator' ) ) {
            $role->add_cap( 'vk_adnetwork_manage_options' );
            $role->add_cap( 'vk_adnetwork_see_interface' );
            $role->add_cap( 'vk_adnetwork_edit_ads' );
            $role->add_cap( 'vk_adnetwork_manage_placements' );
            $role->add_cap( 'vk_adnetwork_place_ads' );
        }
    }

    /**
     * Remove roles and capabilities
     */
    public function remove_capabilities() {
        if ( $role = get_role( 'administrator' ) ) {
            $role->remove_cap( 'vk_adnetwork_manage_options' );
            $role->remove_cap( 'vk_adnetwork_see_interface' );
            $role->remove_cap( 'vk_adnetwork_edit_ads' );
            $role->remove_cap( 'vk_adnetwork_manage_placements' );
            $role->remove_cap( 'vk_adnetwork_place_ads' );
        }
    }

    /**
     * Fired when the plugin is uninstalled.
     */
    public static function uninstall() {
        $vk_adnetwork_options = VK_Adnetwork::get_instance()->options();

        if ( ! empty( $vk_adnetwork_options['vk-adnetwork-uninstall-delete-data'] ) ) {
            global $wpdb;
            $main_blog_id = $wpdb->blogid;

            VK_Adnetwork::get_instance()->create_post_types();

            if ( ! is_multisite() ) {
                self::get_instance()->uninstall_single();
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    self::get_instance()->uninstall_single();
                }
                switch_to_blog( $main_blog_id );
            }
        }

    }

    /**
     * Fired for each blog when the plugin is uninstalled.
     */
    protected function uninstall_single() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", VK_Adnetwork::POST_TYPE_SLUG ) );

        if ( $post_ids ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->delete(
                $wpdb->posts,
                [ 'post_type' => VK_Adnetwork::POST_TYPE_SLUG ],
                [ '%s' ]
            );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id IN( %s )", implode( ',', $post_ids ) ) );
        }

        delete_option( 'vk-adnetwork' );
        delete_option( 'vk-adnetwork-internal' );
        delete_option( 'vk-adnetwork-notices' );
        delete_option( 'vk_adnetwork_ads_txt' );
        delete_option( 'vk-adnetwork-ad-health-notices' );
        delete_option( 'vk-adnetwork-ab-module' );
        delete_option( 'widget_' . VK_Adnetwork_Widget::get_base_id() );
        delete_option( 'vk_adnetwork-ads-placements' );

        // User metadata.
        delete_metadata( 'user', null, 'vk-adnetwork-hide-wizard', '', true );
        delete_metadata( 'user', null, 'vk-adnetwork-ad-list-screen-options', '', true );
        delete_metadata( 'user', null, 'vk-adnetwork-admin-settings', '', true );
        delete_metadata( 'user', null, 'vk-adnetwork-role', '', true );
        delete_metadata( 'user', null, 'edit_vk_adnetwork_per_page', '', true );
        delete_metadata( 'user', null, 'meta-box-order_vk_adnetwork', '', true );
        delete_metadata( 'user', null, 'screen_layout_vk_adnetwork', '', true );
        delete_metadata( 'user', null, 'closedpostboxes_vk_adnetwork', '', true );
        delete_metadata( 'user', null, 'metaboxhidden_vk_adnetwork', '', true );

        // Post metadata.
        delete_metadata( 'post', null, '_vk_adnetwork_ad_settings', '', true );

        // Transients.
        delete_transient( 'vk-adnetwork_add-on-updates-checked' );

        do_action( 'vk-adnetwork-uninstall' );

        wp_cache_flush();
    }

    /**
     * Get the correct support URL: wp.org for free users and website for those with any add-on installed
     *
     * @return string URL.
     */
    public static function support_url() {
        return esc_url( admin_url( 'admin.php?page=vk-adnetwork-support' ) );
    }

    /**
     * Create a random group
     *
     * @param string $url optional parameter.
     * @param string $ex group.
     *
     * @return bool
     */
    public static function get_group_by_url( $url = '', $ex = 'a' ) {

        $url = self::get_short_url( $url );

        $code = intval( substr( md5( $url ), - 1 ), 16 );

        switch ( $ex ) {
            case 'b':
                return ( $code & 2 ) >> 1; // returns 1 or 0.
            case 'c':
                return ( $code & 4 ) >> 2; // returns 1 or 0.
            case 'd':
                return ( $code & 8 ) >> 3; // returns 1 or 0.
            default:
                return $code & 1; // returns 1 or 0.
        }
    }

    /**
     * Get short version of home_url()
     * remove protocol and www
     * remove slash
     *
     * @param string $url URL to be shortened.
     *
     * @return string
     */
    public static function get_short_url( $url = '' ) {

        $url = empty( $url ) ? home_url() : $url;

        // strip protocols.
        if ( preg_match( '/^(\w[\w\d]*:\/\/)?(www\.)?(.*)$/', trim( $url ), $matches ) ) {
            $url = $matches[3];
        }

        // strip slashes.
        $url = trim( $url, '/' );

        return $url;
    }

    /**
     * Return VK AdNetwork logo in base64 format for use in WP Admin menu.
     *
     * @return string
     */
    public static function get_icon_svg() {
                // //return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' style='&%2310; fill: %23000;&%2310;'%3E%3Cpath fill='%23fff' d='M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z'/%3E%3Cpath fill='%23fff' fill-rule='evenodd' d='M19.444 3.01v1.54h1.551c.197 0 .39.055.554.16a.984.984 0 0 1 .37.435.96.96 0 0 1-.2 1.065l-2.126 2.162a.999.999 0 0 1-.723.303h-2.01l-2.08 2.206c.159.371.24.77.237 1.173a2.91 2.91 0 0 1-.507 1.637c-.33.484-.8.862-1.35 1.085a3.067 3.067 0 0 1-1.739.167 3.029 3.029 0 0 1-1.54-.806 2.928 2.928 0 0 1-.823-1.508 2.89 2.89 0 0 1 .171-1.703 2.96 2.96 0 0 1 1.108-1.322 3.054 3.054 0 0 1 1.671-.496c.277 0 .551.036.817.108l2.39-2.568V5.026a.966.966 0 0 1 .315-.713l2.208-2.05a1.013 1.013 0 0 1 1.083-.183 1 1 0 0 1 .44.36c.107.16.165.346.166.537l.017.033Z' clip-rule='evenodd'/%3E%3C/svg%3E";
                // return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0ibm9uZSIgc3R5bGU9IiYjMTA7IGZpbGw6ICMwMDA7JiMxMDsiPjxwYXRoIGZpbGw9IiNmZmYiIGQ9Ik0xMiAyMGE4IDggMCAxIDAgMC0xNiA4IDggMCAwIDAgMCAxNloiLz48cGF0aCBmaWxsPSIjZmZmIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xOS40NDQgMy4wMXYxLjU0aDEuNTUxYy4xOTcgMCAuMzkuMDU1LjU1NC4xNmEuOTg0Ljk4NCAwIDAgMSAuMzcuNDM1Ljk2Ljk2IDAgMCAxLS4yIDEuMDY1bC0yLjEyNiAyLjE2MmEuOTk5Ljk5OSAwIDAgMS0uNzIzLjMwM2gtMi4wMWwtMi4wOCAyLjIwNmMuMTU5LjM3MS4yNC43Ny4yMzcgMS4xNzNhMi45MSAyLjkxIDAgMCAxLS41MDcgMS42MzdjLS4zMy40ODQtLjguODYyLTEuMzUgMS4wODVhMy4wNjcgMy4wNjcgMCAwIDEtMS43MzkuMTY3IDMuMDI5IDMuMDI5IDAgMCAxLTEuNTQtLjgwNiAyLjkyOCAyLjkyOCAwIDAgMS0uODIzLTEuNTA4IDIuODkgMi44OSAwIDAgMSAuMTcxLTEuNzAzIDIuOTYgMi45NiAwIDAgMSAxLjEwOC0xLjMyMiAzLjA1NCAzLjA1NCAwIDAgMSAxLjY3MS0uNDk2Yy4yNzcgMCAuNTUxLjAzNi44MTcuMTA4bDIuMzktMi41NjhWNS4wMjZhLjk2Ni45NjYgMCAwIDEgLjMxNS0uNzEzbDIuMjA4LTIuMDVhMS4wMTMgMS4wMTMgMCAwIDEgMS4wODMtLjE4MyAxIDEgMCAwIDEgLjQ0LjM2Yy4xMDcuMTYuMTY1LjM0Ni4xNjYuNTM3bC4wMTcuMDMzWiIgY2xpcC1ydWxlPSJldmVub2RkIi8+PC9zdmc+';
        return 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKCSB3aWR0aD0iMTAwJSIgdmlld0JveD0iMCAwIDUxMiA1MTIiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDUxMiA1MTIiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8cGF0aCBmaWxsPSIjMUMxQjNBIiBvcGFjaXR5PSIxLjAwMDAwMCIgc3Ryb2tlPSJub25lIgoJZD0iCk0xMzAuMTIyODk0LDM0MC44MjcwMjYgCglDMTQ2LjI4NzE1NSwzNjkuMDA0NzAwIDE2OS40OTk0NjYsMzg4LjM5MjEyMCAxOTkuMzAxMTE3LDQwMC4wNDU5MjkgCglDMjE0LjgxMTI0OSw0MDYuMTExMDUzIDIzMS4wMjEzNjIsNDA5LjI4MjYyMyAyNDcuNTU4OTQ1LDQwOC44MTI2MjIgCglDMjY2LjcxMzAxMyw0MDguMjY4MjUwIDI4NS43MDUyMDAsNDA1LjQ2NTM5MyAzMDIuODExODU5LDM5NS45NDQwOTIgCglDMzEzLjI4MjU2MiwzOTAuMTE2MjExIDMyMy45OTk5MDgsMzg0LjMyNTc0NSAzMzMuMjg0NTE1LDM3Ni44NzE4ODcgCglDMzQ1LjgyOTIyNCwzNjYuODAwNjI5IDM1Ni4xMTY2MDgsMzU0LjM3NjYxNyAzNjQuMTcyMTUwLDM0MC4yMTM2NTQgCglDMzcyLjkyMjYzOCwzMjQuODI4NzY2IDM3OC4yOTIwNTMsMzA4LjMwODgwNyAzODAuNzAxNzUyLDI5MC45Mjc3NjUgCglDMzgyLjU5OTgyMywyNzcuMjM2OTA4IDM4Mi4yMzA1MzAsMjYzLjM2MzIyMCAzNzkuNTYxNDkzLDI0OS43NzM3MTIgCglDMzc2LjQ5ODM4MywyMzQuMTc3ODU2IDM4NC44NDA0NTQsMjE4LjAzMTc2OSA0MDAuMjA5ODM5LDIxNC41ODE4NzkgCglDNDE2LjcwNzE1MywyMTAuODc4Nzk5IDQzMi41MDE5MjMsMjE4LjA2MTc5OCA0MzYuNzEyMjUwLDIzNy4wNDY0MDIgCglDNDQxLjA4MzA2OSwyNTYuNzU0ODIyIDQ0MS4zODgzOTcsMjc2LjkyOTIzMCA0MzguODg0Mjc3LDI5Ni45NDg4MjIgCglDNDMzLjAzNTE4NywzNDMuNzEwMjM2IDQxMi43OTMzMDQsMzgzLjM1MTEzNSAzNzguNDA4NTA4LDQxNS41Njk5MTYgCglDMzUyLjkzNDgxNCw0MzkuNDM4OTY1IDMyMi44NDE3NjYsNDU0Ljg5OTkzMyAyODguOTE3MzU4LDQ2Mi44MzI4ODYgCglDMjY5LjQ0NjMyMCw0NjcuMzg2MDc4IDI0OS41MjY5MzIsNDY4LjI0MzQzOSAyMjkuNzU2MjU2LDQ2Ni43MDcxODQgCglDMjA4LjczOTAyOSw0NjUuMDc0MTI3IDE4OC40NTk3MDIsNDU5LjUxMzk3NyAxNjkuMDI5NzcwLDQ1MC45NjA2MzIgCglDMTUyLjI4NDAxMiw0NDMuNTg4ODk4IDEzNi45NDA4NzIsNDM0LjE3MDIyNyAxMjIuOTc3NzMwLDQyMi4zNTI5MDUgCglDMTA1LjUyMDc2Nyw0MDcuNTc4NzM1IDkwLjc4Nzc1OCwzOTAuNTk2NjE5IDc5LjQzNzk5NiwzNzAuNjgwMTQ1IAoJQzY5Ljk4MDE4NiwzNTQuMDgzNjE4IDYyLjg3ODAxNCwzMzYuNjMzNTQ1IDU4LjYyOTI1MCwzMTcuOTY0MjAzIAoJQzU1LjAxODIzOCwzMDIuMDk3MTM3IDY1Ljk4OTM0MiwyODUuMzI5MDEwIDgyLjMxNzM5MCwyODIuNzY2OTk4IAoJQzk5LjQ4MzkxNywyODAuMDczMzk1IDExMi40MzgyNDgsMjkyLjQ4MzU4MiAxMTYuMjQxMTEyLDMwNS45NDQxODMgCglDMTE5LjU5MzIwOCwzMTcuODA5MjM1IDEyNS4yNzE3NjcsMzI5LjAxNzAyOSAxMzAuMTIyODk0LDM0MC44MjcwMjYgCnoiLz4KPHBhdGggZmlsbD0iIzFDMUIzQSIgb3BhY2l0eT0iMS4wMDAwMDAiIHN0cm9rZT0ibm9uZSIKCWQ9IgpNMjQxLjIyMzMyOCwyMTIuOTk4NTM1IAoJQzI0OS4wODIwNjIsMjEzLjUwNTg5MCAyNTYuNDYzOTg5LDIxNC4wODkyNDkgMjYzLjg1NjkwMywyMTQuNDQyODEwIAoJQzI2NS4wMzAzMzQsMjE0LjQ5ODkxNyAyNjYuNTUwMjkzLDIxMy44MjE4MjMgMjY3LjQwOTI0MSwyMTIuOTc1NDc5IAoJQzI4My4xODg5OTUsMTk3LjQyNjg5NSAyOTguODk0ODY3LDE4MS44MDMyODQgMzE0LjYxMDg3MCwxNjYuMTg5OTg3IAoJQzMyMS45MjUyNjIsMTU4LjkyMzQ0NyAzMjkuMzI3MjQwLDE1MS43NDAwODIgMzM2LjQ1MTE3MiwxNDQuMjkxMDc3IAoJQzMzNy44NTk1ODksMTQyLjgxODM1OSAzMzguODM4MTY1LDE0MC4zMTkyMTQgMzM4Ljg4NzY2NSwxMzguMjY2MjM1IAoJQzMzOS4xMzI1MzgsMTI4LjEwOTgzMyAzMzguOTc2ODk4LDExNy45NDQyNjAgMzM5LjAwNDgyMiwxMDcuNzgyMDQzIAoJQzMzOS4wMzc3MjAsOTUuODE0MzYyIDM0My4wODEzMjksODUuNDUxMzI0IDM1MS41NzU5ODksNzYuOTM3MTcyIAoJQzM2Ny4xMDY5NjQsNjEuMzcwNjA5IDM4Mi42ODAzNTksNDUuODQ2MzY3IDM5OC4xOTk4MjksMzAuMjY4MzUxIAoJQzQwMS40MzM2ODUsMjcuMDIyMzA2IDQwNS41MzYwMTEsMjYuMDQyNTExIDQwOS40NzA3MzQsMjcuNDAxOTI2IAoJQzQxMy40NzQwNjAsMjguNzg1MDIzIDQxNC4wNTExMTcsMzIuODI3MTQ1IDQxNC4wMzIyODgsMzYuODI0NjY5IAoJQzQxMy45NTc3MzMsNTIuNjUwODM3IDQxMy45NTI0NTQsNjguNDc3ODIxIDQxNC4wMzQ5NzMsODQuMzAzOTAyIAoJQzQxNC4wNTY1NDksODguNDQwNDMwIDQxNC4zNTk3NzIsOTIuNTg3MTU4IDQxNC43NTc2MjksOTYuNzA3NTczIAoJQzQxNS41NTgyMjgsMTA0Ljk5OTQwNSA0MTguODM0ODA4LDEwNy45Njk1NTEgNDI3LjA4MzQ2NiwxMDcuOTk4NjgwIAoJQzQ0My41NzYwMTksMTA4LjA1NjkwOCA0NjAuMDY5MTUzLDEwOC4wMTA5MTAgNDc2LjU2MTAzNSwxMDguMTQwNTQxIAoJQzQ4MC44MjM4ODMsMTA4LjE3NDA0OSA0ODUuMDk2MDM5LDEwOC41NzU5NTggNDg5LjM0MDMwMiwxMDkuMDI5NzA5IAoJQzQ5NC41Nzg3MzUsMTA5LjU4OTc2MCA0OTcuMTg1MjcyLDExMy40NTE3ODIgNDk1LjY1NDM4OCwxMTguNDM4ODY2IAoJQzQ5NS4wNDM0NTcsMTIwLjQyOTAwOCA0OTQuMjU0MTUwLDEyMi42NTEyNDUgNDkyLjg0MjI1NSwxMjQuMDY0ODQyIAoJQzQ3Ni40OTQzODUsMTQwLjQzMjA2OCA0NjAuMjM0NzExLDE1Ni44OTkzODQgNDQzLjQ2MzAxMywxNzIuODI1NjUzIAoJQzQzNS45NzMyMDYsMTc5LjkzNzkyNyA0MjYuMjY0ODYyLDE4Mi44ODc4OTQgNDE1Ljg4MzU0NSwxODIuOTgwNjA2IAoJQzQwNS41NTU0ODEsMTgzLjA3Mjg3NiAzOTUuMjIzMTE0LDE4Mi44NzQ4MTcgMzg0Ljg5OTQ0NSwxODMuMTA2OTAzIAoJQzM4Mi44MzE5MDksMTgzLjE1MzM2NiAzODAuMjU4Nzg5LDE4NC4wMTc5MTQgMzc4LjgyNDAwNSwxODUuNDI2OTg3IAoJQzM1NS4xODcxMzQsMjA4LjY0MDA5MSAzMzEuNzUxOTUzLDIzMi4wNTg1MzMgMzA4LjExMDUwNCwyNTUuMjY2OTY4IAoJQzMwNC41MzE1NTUsMjU4Ljc4MDM2NSAzMDYuODM0NTk1LDI2Mi41NzIyOTYgMzA3LjI2NzgyMiwyNjUuNzExNzYxIAoJQzMwOS4yNTQ3MDAsMjgwLjExMDI2MCAzMDUuODc4MDgyLDI5My4wNzgwNjQgMjk4LjI1Mzk2NywzMDUuMDQ3MzAyIAoJQzI4OC44MzUxNzUsMzE5LjgzMzk1NCAyNzUuMDg3NjE2LDMyOS40NTgyMjEgMjU3Ljk5NjU1MiwzMzEuNjM2ODEwIAoJQzIzMi4yODAwNDUsMzM0LjkxNDkxNyAyMTAuMjIyNjI2LDMyNy40MDEwOTMgMTk1LjQ3MjE5OCwzMDEuNTkzODQyIAoJQzE4NS4xNzkyNDUsMjgzLjU4NTMyNyAxODQuOTY1NDI0LDI2NC40NzExMzAgMTkzLjgxNzc5NSwyNDYuMTU4MjAzIAoJQzIwMy4xMjI1NzQsMjI2LjkwOTM5MyAyMTkuMTI4MDM2LDIxNS41OTI5NzIgMjQxLjIyMzMyOCwyMTIuOTk4NTM1IAp6Ii8+CjxwYXRoIGZpbGw9IiMxQzFCM0EiIG9wYWNpdHk9IjEuMDAwMDAwIiBzdHJva2U9Im5vbmUiCglkPSIKTTIyMC44NzEyNzcsMTQxLjkyMTQ2MyAKCUMyMDUuNTA1NDE3LDE0NC4zOTE2MTcgMTkxLjc2MzIyOSwxNTAuMDk2MjY4IDE3OC43OTI3NzAsMTU3LjY3MTc5OSAKCUMxNTcuMzQxNjE0LDE3MC4yMDA2MzggMTQwLjc0MDUyNCwxODcuNDM5NTQ1IDEyOC43MjU3MjMsMjA5LjI1MTI4MiAKCUMxMTguNjA3MDcxLDIyNy42MjA3NDMgOTUuNzAyMTI2LDIzMC42NDA2NTYgODEuMDcxNzU0LDIxNi4xNTkxMTkgCglDNzQuMDQwMzE0LDIwOS4xOTkxODggNjkuNDA1MjI4LDE5NS43NzY0NDMgNzUuOTE2NDEyLDE4NC4xMDI4MjkgCglDODEuOTM3ODM2LDE3My4zMDcyNjYgODkuMDc0NzkxLDE2My4xMDY5MDMgOTYuMTE5NTgzLDE1Mi45MjA0MTAgCglDOTkuNzI1NDk0LDE0Ny43MDY0MjEgMTAzLjY2NTg0OCwxNDIuNDgwNjM3IDEwOC4zOTg2MzYsMTM4LjMzODI0MiAKCUMxMTkuMTk3NDAzLDEyOC44ODY1ODEgMTI5Ljk4MjQyMiwxMTkuMjA3MTg0IDE0MS44NzUwNjEsMTExLjI3MTkxOSAKCUMxNTYuMTkzMzI5LDEwMS43MTgxNzggMTcxLjg0Nzg4NSw5NC4xMTcwNzMgMTg4LjU0ODgxMyw4OS41MTgwNTkgCglDMjAwLjI4MzY0Niw4Ni4yODY1NjggMjEyLjMwOTczOCw4My42NjMwNzEgMjI0LjM3OTM2NCw4Mi4yMjU5MjIgCglDMjM1Ljk5NDIzMiw4MC44NDI5MTggMjQ3Ljg3OTY1NCw4MC41Mjk5OTEgMjU5LjU1OTIzNSw4MS4yMTk2ODggCglDMjcyLjAzNjg2NSw4MS45NTY1MTIgMjg0Ljg2NTMyNiw4Mi4zOTY3NzQgMjk2LjM4NzA1NCw4OC4zMDQ3MjYgCglDMzA2LjEzNTcxMiw5My4zMDM1MTMgMzEyLjQ4ODY3OCwxMDYuODMzODM5IDMxMC43NjU5NjEsMTE3LjI0OTEzMCAKCUMzMDguMzAyNzk1LDEzMi4xNDE1MTAgMjk3LjMyNTgzNiwxMzkuODQ3NDg4IDI4Ny4yOTQ1MjUsMTQyLjE1ODIzNCAKCUMyNzkuMzY4MDQyLDE0My45ODQxMzEgMjcyLjIzNDQ5NywxNDAuNjg5ODM1IDI2NC43MjU4NjEsMTQwLjQzMTUxOSAKCUMyNTIuNjE5Mzg1LDE0MC4wMTUwMTUgMjQwLjQ4NzkxNSwxNDAuMjA1ODI2IDIyOC4zNjk1OTgsMTQwLjM1MzI3MSAKCUMyMjYuMDA5NzIwLDE0MC4zODE5NzMgMjIzLjY2MTY4MiwxNDEuMzgzMTc5IDIyMC44NzEyNzcsMTQxLjkyMTQ2MyAKeiIvPgo8L3N2Zz4=';
    }

    /**
     * Fires when a post is transitioned from one status to another.
     *
     * @param string  $new_status New post status.
     * @param string  $old_status Old post status.
     * @param WP_Post $post       Post object.
     */
    public function transition_ad_status( $new_status, $old_status, $post ) {
        if ( ! isset( $post->post_type ) || VK_Adnetwork::POST_TYPE_SLUG !== $post->post_type || ! isset( $post->ID ) ) {
            return;
        }

        $ad = new VK_Adnetwork_Ad( $post->ID );

        if ( $old_status !== $new_status ) {
            /**
             * Fires when an ad has transitioned from one status to another.
             *
             * @param VK_Adnetwork_Ad $ad Ad object.
             */
            do_action( "vk-adnetwork-ad-status-$old_status-to-$new_status", $ad );
        }

        if ( 'publish' === $new_status && 'publish' !== $old_status ) {
            /**
             * Fires when an ad has transitioned from any other status to `publish`.
             *
             * @param VK_Adnetwork_Ad $ad Ad object.
             */
            do_action( 'vk-adnetwork-ad-status-published', $ad );
        }

        if ( 'publish' === $old_status && 'publish' !== $new_status ) {
            /**
             * Fires when an ad has transitioned from `publish` to any other status.
             *
             * @param VK_Adnetwork_Ad $ad Ad object.
             */
            do_action( 'vk-adnetwork-ad-status-unpublished', $ad );
        }

        if ( $old_status === 'publish' && $new_status === VK_Adnetwork_Ad_Expiration::POST_STATUS ) {
            /**
             * Fires when an ad is expired.
             *
             * @param int             $id
             * @param VK_Adnetwork_Ad $ad
             */
            do_action( 'vk-adnetwork-ad-expired', $ad->id, $ad );
        }
    }

}
