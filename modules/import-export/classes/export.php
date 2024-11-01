<?php
/**
 * Export functionality.
 */
class VK_Adnetwork_Export {
    /**
     * @var VK_Adnetwork_Export
     */
    private static $instance;

    /**
     * Status messages
     */
    private $messages = [];

    private function __construct() {

        $page_hook = 'admin_page_vk-adnetwork-import-export';       // import & export. /wp-admin/admin.php?page=vk-adnetwork-import-export
        // execute before headers are sent
        add_action( 'load-' . $page_hook, [ $this, 'download_export_file' ] );
    }

    /**
     * Return an instance of this class.
     */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Handle form submissions
     */
    public function download_export_file() {
        $action = VK_Adnetwork_Admin::get_instance()->current_action();

        if ( $action === 'export' ) {
            if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options') ) ) {
                return;
            }

            check_admin_referer( 'vk_adnetwork-export' );

            $content = []; // instead VK_Adnetwork_Utils::vk_adnetwork_sanitize_array()
            if ( isset( $_POST['content'] ) && is_array( $_REQUEST['content'] ) ) {
                foreach (['ads', 'placements', 'options'] as $keycontent) {
                    if (in_array($keycontent, $_REQUEST['content']))
                        $content[] = $keycontent;
                }
                $this->process( $content );
            }
        }
    }

    /**
     * Generate XML file.
     *
     * @param array $content Types of content to be exported.
     */
    private function process( array $content ) {
        global $wpdb;

        // @set-time-limit( 0 );
        // @ini-set( 'memory-limit', apply-filters( 'admin-memory-limit', WP-MAX-MEMORY-LIMIT ) );

        $export = [];

        if ( in_array( 'ads', $content ) ) {

            $ads = [];
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $posts = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT
                        ID,
                        post_date,
                        post_date_gmt,
                        post_content,
                        post_title,
                        post_password,
                        post_name,
                        post_status,
                        post_modified,
                        post_modified_gmt,
                        guid
                    FROM $wpdb->posts 
                    WHERE post_type = %s 
                      AND post_status NOT IN ('trash', 'auto-draft')
                    ",
                    VK_Adnetwork::POST_TYPE_SLUG
                ),
                ARRAY_A
            );

            $mime_types = array_filter( get_allowed_mime_types(), function( $mime_type ) {
                return preg_match( '/image\//', $mime_type );
            } );
            $search     = '/' . preg_quote( home_url(), '/' ) . '(\S+?)\.(' . implode( '|', array_keys( $mime_types ) ) . ')/i';
            foreach ( $posts as $k => $post ) {
                if ( ! empty( $post['post_content'] ) ) {
                    // wrap images in <vk_adnetwork_import_img></vk_adnetwork_import_img> tags
                    $post['post_content']  = preg_replace( $search, '<vk_adnetwork_import_img>\\0</vk_adnetwork_import_img>', $post['post_content']  );
                }

                $ads[$k] = $post;
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d", absint( $post['ID'] ) ) );

                foreach ( $postmeta as $meta ) {
                    if ( $meta->meta_key === '_edit_lock' ) {
                        continue;
                    }
                    if ( $meta->meta_key === VK_Adnetwork_Ad::$options_meta_field ) {
                        $ad_options = maybe_unserialize( $meta->meta_value );
                        if ( isset( $ad_options['output']['image_id'] ) ) {
                            $image_id = absint( $ad_options['output']['image_id'] );
                            if ( $atached_img = wp_get_attachment_url( $image_id) ) {
                                $ads[ $k ]['attached_img_url'] = $atached_img;
                            }
                        }
                        $ads[ $k ]['meta_input'][ $meta->meta_key ] = $ad_options;
                    } else {
                        $ads[ $k ]['meta_input'] [$meta->meta_key ] = $meta->meta_value;
                    }
                }
            }

            if ( $ads ) {
                $export['ads'] = $ads;
            }
        }

        if ( in_array( 'placements', $content ) ) {
            $placements = VK_Adnetwork::get_instance()->get_model()->get_ad_placements_array();

            // prevent nodes starting with number
            foreach ( $placements as $key => &$placement ) {
                $placement['key'] = $key;
            }

            $export['placements'] = array_values( $placements );
        }

        if ( in_array( 'options', $content, true ) ) {
            /**
             * Filters the list of options to be exported.
             *
             * @param array $options An array of options
             */
            $export['options'] = array_filter( apply_filters( 'vk-adnetwork-export-options', [ VK_ADNETWORK_SLUG => get_option(VK_ADNETWORK_SLUG) ] ) );
        }

        do_action_ref_array( 'vk-adnetwork-export', [ $content, &$export ] );

        if ( $export ) {
            if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                error_log( print_r( 'Array to decode', true ) );
                error_log( print_r( $export, true) );
            }

            // add the root domain and the current date to the filename.
            $filename = sprintf(
                '%s-vk-adnetwork-export-%s.xml',
                sanitize_title( preg_replace(
                    '#^(?:[^:]+:)?//(?:www\.)?([^/]+)#',
                    '$1',
                    get_bloginfo( 'url' )
                ) ),
                gmdate( 'Y-m-d' )
            );

            try {
                $encoded = VK_Adnetwork_XmlEncoder::get_instance()->encode( $export, [ 'encoding' => get_option( 'blog_charset' ) ] );

                header( 'Content-Description: File Transfer' );
                header( 'Content-Disposition: attachment; filename=' . $filename );
                header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
                // атличный хук!)) как И использовать ихнюю функцию, И ничего не испортить (всё вернуть как было!) (портит только амперсанды -- в изначальном XML они &#038;)
                // this is a secure XML generated by DOMDocument->buildXml
                echo html_entity_decode(esc_xml($encoded)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                    error_log( print_r( $encoded, true ) );
                    $decoded = VK_Adnetwork_XmlEncoder::get_instance()->decode( $encoded );
                    error_log( 'result ' . var_export( $export === $decoded , true ) );
                }

                exit();

            } catch ( Exception $e ) {
                $this->messages[] = [ 'error', $e->getMessage() ];
            }
        }
    }

    public function get_messages(){
        return $this->messages;
    }
}
