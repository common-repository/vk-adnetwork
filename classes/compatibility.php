<?php
/**
 * Compatibility fixes with other plugins.
 */
class VK_Adnetwork_Compatibility {
    /**
     * Array that holds strings that should not be optimized by other plugins.
     *
     * @var array
     */
    private $critical_inline_js;

    /**
     * VK_Adnetwork_Compatibility constructor.
     */
    public function __construct() {
        // Elementor plugin.
        if ( defined( 'ELEMENTOR_VERSION' ) ) {
            add_filter(
                'vk-adnetwork-placement-content-injection-xpath',
                [
                    $this,
                    'content_injection_elementor',
                ],
                10,
                1
            );
        }
        // WP Rocket
        add_filter( 'rocket_excluded_inline_js_content', [ $this, 'rocket_exclude_inline_js' ] );
        add_filter( 'rocket_delay_js_exclusions', [ $this, 'rocket_exclude_inline_js' ] );
        // WPML.
        add_filter( 'wpml_admin_language_switcher_active_languages', [ $this, 'wpml_language_switcher' ] );
        // WordPress SEO by Yoast.
        add_filter( 'wpseo_sitemap_entry', [ $this, 'wordpress_seo_noindex_ad_attachments' ], 10, 3 );
        // Add shortcode for MailPoet.
        add_filter( 'mailpoet_newsletter_shortcode', [ 'VK_Adnetwork_Compatibility', 'mailpoet_ad_shortcode' ], 10, 5 );

        add_action( 'admin_enqueue_scripts', [ $this, 'admin_dequeue_scripts_and_styles' ], 100 );

        // Make sure inline JS in head is executed when Complianz is set to block JS.
        add_filter( 'cmplz_script_class', [ $this, 'complianz_exclude_inline_js' ], 10, 2 );

        $this->critical_inline_js = $this->critical_inline_js();
    }

    /**
     * Modify xPath expression for Elementor plugin.
     * The plugin does not wrap newly created text in 'p' tags.
     *
     * @param string $tag xpath tag.
     * @return string xPath expression
     */
    public function content_injection_elementor( $tag ) {
        if ( 'p' === $tag ) {
            // 'p' or 'div.elementor-widget-text-editor' without nested 'p'
            $tag = "*[self::p or self::div[@class and contains(concat(' ', normalize-space(@class), ' '), ' elementor-widget-text-editor ') and not(descendant::p)]]";
        }

        return $tag;
    }

    /**
     * Prevent the 'vk_adnetwork_ready' function declaration from being merged with other JS
     * and outputted into the footer. This is needed because WP Rocket does not output all
     * the code that depends on this function into the footer.
     *
     * @param array $exclusions Patterns to match in inline JS content.
     *
     * @return array
     */
    public function rocket_exclude_inline_js( $exclusions ) {
        return array_merge( $exclusions, $this->critical_inline_js );
    }

    /**
     * Prevent Complianz from suppressing our head inline script.
     *
     * @param string $class       the class Complianz adds to the script, `cmplz-script` for prevented scripts, `cmplz-native` for allowed.
     * @param string $total_match the script string.
     *
     * @return string
     */
    public function complianz_exclude_inline_js( $class, $total_match ) {
        if ( $class === 'cmplz-native' ) {
            return $class;
        }
        foreach ( $this->critical_inline_js as $critical_inline_js ) {
            if (str_contains($total_match, $critical_inline_js)) {
                return 'cmplz-native';
            }
        }

        return $class;
    }

    /**
     * Compatibility with WPML
     * show only all languages in language switcher on VK AdNetwork pages if ads are translated
     *
     * @param array $active_languages languages that can be used in language switcher.
     * @return array
     */
    public function wpml_language_switcher( $active_languages ) {
        global $sitepress;
        $screen = get_current_screen();
        if ( ! isset( $screen->id ) ) {
            return $active_languages;
        }

        switch ( $screen->id ) {
            // check if VK AdNetwork ad post type is translatable.
            case 'edit-vk_adnetwork':   // ads overview. /wp-admin/edit.php?post_type=vk_adnetwork
            case 'vk_adnetwork':        // ad edit page. /wp-admin/post.php?post=480&action=edit
                $translatable_documents = $sitepress->get_translatable_documents();
                if ( empty( $translatable_documents['vk_adnetwork'] ) ) {
                    return [];
                }
                break;
        }

        return $active_languages;
    }

    /**
     * WordPress SEO: remove attachments attached to ads from `/attachment-sitemap.xml`.
     *
     * @param array  $url  Array of URL parts.
     * @param string $type URL type.
     * @param object $post WP_Post object of attachment.
     * @return array|bool Unmodified array of URL parts or false to remove URL.
     */
    public function wordpress_seo_noindex_ad_attachments( $url, $type, $post ) {
        if ( 'post' !== $type ) {
            return $url;
        }

        static $ad_ids = null;
        if ( null === $ad_ids ) {
            $ad_ids = VK_Adnetwork::get_instance()->get_model()->get_ads(
                [
                    'post_status' => 'any',
                    'fields'      => 'ids',
                ]
            );
        }

        if ( isset( $post->post_parent ) && in_array( $post->post_parent, $ad_ids, true ) ) {
            return false;
        }

        return $url;
    }

    /**
     * Display an ad or ad group in a newsletter created by MailPoet.
     * e.g., [custom:ad:123] to display ad with the ID 123
     * [custom:ad_group:345] to display ad group with the ID 345
     *
     * @param string $shortcode shortcode that placed the ad.
     * @param mixed  $newsletter unused.
     * @param mixed  $subscriber unused.
     * @param mixed  $queue unused.
     * @param string $newsletter_body unused.
     *
     * @return string
     */
    public static function mailpoet_ad_shortcode( $shortcode, $newsletter, $subscriber, $queue, $newsletter_body ) {

            // display individual ad.
        if (str_starts_with($shortcode, '[custom:ad:')) {
            // get ad ID.
            preg_match( '/\d+/', $shortcode, $matches );
            $ad_id = $matches[0];

            // is returning an empty string when the ad is not found good UI?
            if ( empty( $ad_id ) ) {
                return '';
            }

            $ad = new VK_Adnetwork_Ad( $ad_id );
            // only display if the ad type could work, i.e. plain text and image ads.
            if ( isset( $ad->type ) && in_array( $ad->type, [ 'plain', 'image' ], true ) ) {
                return vk_adnetwork_get_ad( $ad_id );
            }

            return '';
        } else {
            // always return the shortcode if it doesn't match your own!
            return $shortcode;
        }
    }

    /**
     * Check if placements of type other than `header` can be injected during `wp_head` action.
     */
    public static function can_inject_during_wp_head() {
        // the "Thrive Theme Builder" theme.
        if ( did_action( 'before_theme_builder_template_render' ) && ! did_action( 'after_theme_builder_template_render' ) ) {
            return true;
        }
        return false;
    }

    /**
     * Dequeue scripts and styles to prevent layout issues.
     */
    public function admin_dequeue_scripts_and_styles() {
        if ( ! VK_Adnetwork_Admin::screen_belongs_to_vk_adnetwork() ) {
            return;
        }

        // Dequeue the css file enqueued by the JNews theme.
        if ( defined( 'JNEWS_THEME_URL' ) ) {
            wp_dequeue_style( 'jnews-admin' );
        }
    }

    /**
     * Get an array of strings to exclude when plugins "optimize" JS.
     *
     * @return array
     */
    private function critical_inline_js() {
        $frontend_prefix = VK_Adnetwork_Plugin::get_instance()->get_frontend_prefix();
        $default         = [ sprintf('id="%s-plugin-script-js-after"', VK_ADNETWORK_SLUG) ];
        /**
         * Filters an array of strings of (inline) JavaScript "identifiers" that should not be "optimized"/delayed etc.
         *
         * @param array $default Array of excluded patterns.
         */
        $exclusions = apply_filters( 'vk-adnetwork-compatibility-critical-inline-js', $default, $frontend_prefix );

        if ( ! is_array( $exclusions ) ) {
            $exclusions = $default;
        }

        return $exclusions;
    }
}
