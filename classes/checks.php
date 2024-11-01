<?php

/**
 * Checks for various things
 *
 * @since 1.6.9
 */
class VK_Adnetwork_Checks {

    /**
     * Minimum required PHP version of VK AdNetwork
     */
    const MINIMUM_PHP_VERSION = '7.4.33';


    /**
     * Show the list of potential issues
     */
    public static function show_issues() {
        include_once VK_ADNETWORK_BASE_PATH . 'admin/views/checks.php';
    }

    /**
     * PHP version minimum
     *
     * @return bool true if uses the minimum PHP version or higher
     */
    public static function php_version_minimum() {

        if ( version_compare( phpversion(), self::MINIMUM_PHP_VERSION, '>=' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Caching used
     *
     * @return bool true if active
     */
    public static function cache() {
        if ( ( defined( 'WP_CACHE' ) && WP_CACHE ) // general cache constant.
            || defined( 'W3TC' ) // W3 Total Cache.
            || function_exists( 'wp_super_cache_text_domain' ) // WP SUper Cache.
            || defined( 'WP_ROCKET_SLUG' ) // WP Rocket.
            || defined( 'WPFC_WP_CONTENT_DIR' ) // WP Fastest Cache.
            || class_exists( 'HyperCache', false ) // Hyper Cache.
            || defined( 'CE_CACHE_DIR' ) // Cache Enabler.
        ) {
            return true;
        }

        return false;
    }

    /**
     * Autoptimize plugin installed
     *   can change ad tags, especially inline css and scripts
     *
     * @link https://wordpress.org/plugins/autoptimize/
     * @return bool true if Autoptimize is installed
     */
    public static function active_autoptimize() {

        if ( defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
            return true;
        }

        return false;
    }

    /**
     * WP rocket plugin installed
     *
     * @return bool true if WP rocket is installed
     */
    public static function active_wp_rocket() {
        if ( defined( 'WP_ROCKET_SLUG' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Checks the settings of wp rocket to find out if combining of javascript files is enabled
     *
     * @return boolean true, when "Combine JavaScript files" is enabled
     */
    public static function is_wp_rocket_combine_js_enabled() {
        if ( self::active_wp_rocket() ) {
            $settings = get_option( 'wp_rocket_settings' );
            if ( $settings ) {
                if ( isset( $settings['minify_concatenate_js'] ) && $settings['minify_concatenate_js'] ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Any AMP plugin enabled
     *
     * @return bool true if AMP plugin is installed
     */
    public static function active_amp_plugin() {
        // Accelerated Mobile Pages.
        if ( function_exists( 'ampforwp_is_amp_endpoint' ) ) {
            return true;
        }

        // AMP plugin.
        if ( function_exists( 'is_amp_endpoint' ) ) {
            return true;
        }

        // other plugins.
        if ( function_exists( 'is_wp_amp' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the preconditions are met to wrap an ad with <!--noptimize--> comments
     *
     * @return boolean
     */
    public static function requires_noptimize_wrapping() {
        return self::active_autoptimize() || self::is_wp_rocket_combine_js_enabled();
    }

    /**
     * Check for additional conflicting plugins
     *
     * @return array $plugins names of conflicting plugins
     */
    public static function conflicting_plugins() {
        $conflicting_plugins = [];

        if ( defined( 'Publicize_Base' ) ) { // JetPack Publicize module.
            $conflicting_plugins[] = 'Jetpack – Publicize';
        }
        if ( defined( 'PF__PLUGIN_DIR' ) ) { // Facebook Instant Articles & Google AMP Pages by PageFrog.
            $conflicting_plugins[] = 'Facebook Instant Articles & Google AMP Pages by PageFrog';
        }
        if ( defined( 'GT_VERSION' ) ) { // GT ShortCodes.
            $conflicting_plugins[] = 'GT ShortCodes';
        }
        if ( class_exists( 'SimilarPosts', false ) ) { // Similar Posts, https://de.wordpress.org/plugins/similar-posts/.
            $conflicting_plugins[] = 'Similar Posts';
        }

        return $conflicting_plugins;
    }

    /**
     * Check if any of the global hide ads options is set
     * ignore RSS feed setting, because it is standard
     *
     * @since 1.7.10
     * @return bool
     */
    public static function ads_disabled() {
        $options = VK_Adnetwork::get_instance()->options();
        if ( isset( $options['vk-adnetwork-disabled-ads'] ) && is_array( $options['vk-adnetwork-disabled-ads'] ) ) {
            foreach ( $options['vk-adnetwork-disabled-ads'] as $_key => $_value ) {
                // don’t warn if "RSS Feed", "404", or "REST API" option are enabled, because they are normally not critical.
                if ( ! empty( $_value ) && ! in_array( (string) $_key, [ 'feed', '404', 'rest-api' ], true ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check for required php extensions
     *
     * @return array
     */
    public static function php_extensions() {

        $missing_extensions = [];

        if ( ! extension_loaded( 'dom' ) ) {
            $missing_extensions[] = 'dom';
        }

        if ( ! extension_loaded( 'mbstring' ) ) {
            $missing_extensions[] = 'mbstring';
        }

        return $missing_extensions;
    }

    /**
     * Get the list of VK AdNetwork constant defined by the user.
     *
     * @return array
     */
    public static function get_defined_constants() {
        $constants = apply_filters(
            'vk-adnetwork-constants',
            [
                'VK_ADNETWORK_ADS_DISABLED',
                'VK_ADNETWORK_DISABLE_RESPONSIVE_IMAGES',
                'VK_ADNETWORK_AD_DEBUG_FOR_ADMIN_ONLY',
                'VK_ADNETWORK_DISABLE_ANALYTICS_ANONYMIZE_IP',
                'VK_ADNETWORK_DISABLE_CHANGE',
                'VK_ADNETWORK_DISABLE_CODE_HIGHLIGHTING',
                'VK_ADNETWORK_DISABLE_SHORTCODE_BUTTON',
                'VK_ADNETWORK_DISALLOW_PHP',
                'VK_ADNETWORK_ENABLE_REVISIONS',
                'VK_ADNETWORK_GEO_TEST_IP',
                'VK_ADNETWORK_RESPONSIVE_DISABLE_BROWSER_WIDTH',
                'VK_ADNETWORK_SUPPRESS_PLUGIN_ERROR_NOTICES',
                'VK_ADNETWORK_TRACKING_DEBUG',
                'VK_ADNETWORK_TRACKING_NO_HOURLY_LIMIT',
            ]
        );

        $result = [];
        foreach ( $constants as $constant ) {
            if ( defined( $constant ) ) {
                $result[] = $constant;
            }
        }
        return $result;
    }


    /**
     * WP Engine hosting detected
     *
     * @return bool true if site is hosted by WP Engine
     */
    public static function wp_engine_hosting() {
        if ( defined( 'WPE_APIKEY' ) ) {
            return true;
        }

        return false;
    }

}
