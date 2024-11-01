<?php

/**
 * Class VK_Adnetwork_Import
 */
class VK_Adnetwork_Import {
    /**
     * @var VK_Adnetwork_Export
     */
    private static $instance;

    /**
     * Uploaded XML file path
     */
    private $import_id;

    /**
     * Status messages
     */
    private $messages = [];

    /**
     * Imported data mapped with previous data, e.g. ['ads'][ new_ad_id => old_ad_id (or null if does not exist) ]
     */
    public $imported_data = [
        'ads' => [],
        'placements' => []
    ];

    /**
     * Attachments, created for Image Ads and images in ad content
     */
    private $created_attachments = [];

    private function __construct() {}

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
     * Manages stages of the XML import process
     */
    public function dispatch() {
        if ( ! isset( $_POST['_wpnonce'] )
            || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'vk_adnetwork-import' )
            || ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options') ) ) {
            return;
        }

        if ( ! isset( $_POST['import_type'] ) ) {
            return;
        }

        switch ( $_POST['import_type'] ) {
            case 'xml_content':
                if ( empty( $_POST['xml_textarea'] ) ) {
                    $this->messages[] = [ 'error', esc_html__( 'Please enter XML content', 'vk-adnetwork' ) ];
                    return;
                }
//              еще один пздц -- надо санитайзить XML обязательно именно функицей вордпресса, но такой у них НЕТ!
//              $content = stripslashes( _POST[xml_textarea] ); // the correct format of the XML import file is checked here -- https://www.php.net/manual/en/domdocument.loadxml.php
//              $this->import( $content );
                break;
            case 'xml_file':
                if ( $this->handle_upload() ) {
                    $content = file_get_contents( $this->import_id ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                    $this->import( $content );
                    @wp_delete_file( $this->import_id );
                }
                break;
        }
    }

    /**
     * The main controller for the actual import stage
     *
     * @param string $xml_content XML content to import.
     */
    public function import( &$xml_content ) {
        // @set-time-limit( 0 );
        // @ini-set( 'memory-limit', apply-filters( 'admin-memory-limit', WP-MAX-MEMORY-LIMIT ) );

        $xml_content = trim( $xml_content );

        if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
            error_log( 'source XML:' );
            error_log( $xml_content );
        }

        try {
            $decoded = VK_Adnetwork_XmlEncoder::get_instance()->decode( $xml_content );
        } catch ( Exception $e ) {
            error_log( $e->getMessage() );
            $this->messages[] =  [ 'error', $e->getMessage() ];
            return;
        }

        if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
            error_log( 'decoded XML:' );
            error_log(print_r($decoded, true));
        }

        $post_ids = $this->import_ads( $decoded );
        $this->import_placements( $decoded );
        $this->import_options( $decoded );

        do_action_ref_array( 'vk-adnetwork-import', [ &$decoded, &$this->imported_data, &$this->messages ] );

        wp_cache_flush();

        return $post_ids;
    }

    /**
     * Create new ads based on import information
     *
     * @param array $decoded decoded XML.
     */
    private function import_ads( &$decoded ) {
        if ( isset( $decoded['ads'] ) && is_array( $decoded['ads'] ) ) {
            foreach ( $decoded['ads'] as $k => $ad ) {
                $ad_title = $ad['post_title'] ?? '';
                $ad_date = $ad['post_date'] ?? '';

                if ( isset( $ad['meta_input'] ) && is_array( $ad['meta_input'] ) ) {
                    foreach ( $ad['meta_input'] as $meta_k => &$meta_v ) {
                        if ( $meta_k !== VK_Adnetwork_Ad::$options_meta_field ) {
                            $meta_v = maybe_unserialize( $meta_v );
                        }
                    }
                }

                $insert_ad = [
                    'post_title' => $ad_title,
                    'post_date' => $ad_date,
                    'post_date_gmt' => $ad['post_date_gmt'] ?? '',
                    'post_content' => isset( $ad['post_content'] ) ? $this->process_ad_content( $ad['post_content'] ) : '',
                    'post_password' => $ad['post_password'] ?? '',
                    'post_name' => $ad['post_name'] ?? '',
                    'post_status' => $ad['post_status'] ?? 'publish',
                    'post_modified' => $ad['post_modified'] ?? '',
                    'post_modified_gmt' => $ad['post_modified_gmt'] ?? '',
                    'guid' => $ad['guid'] ?? '',
                    'post_author' => get_current_user_id(),
                    'post_type' => VK_Adnetwork::POST_TYPE_SLUG,
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                    'meta_input' => $ad['meta_input'] ?? '',
                ];


                $post_id = wp_insert_post( $insert_ad, true );

                if ( is_wp_error( $post_id ) ) {
                    // Не удался импорт <em>%s</em>
                    // translators: %s is the ad-post title
                    $this->messages[] = [ 'error', wp_kses(sprintf( __( 'Failed to import <em>%s</em>', 'vk-adnetwork' ), esc_html($ad['post_title'] ) ), ['em' => true] ) ];
                    if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG ) {
                        $this->messages[] = [ 'error', ' > ' . $post_id->get_error_message() ];
                    }

                    continue;
                } else {
                    $link = ( $link = get_edit_post_link( $post_id ) ) ? sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html__( 'Edit', 'vk-adnetwork' ) ) : '';
                    // Новый рекламный блок создан: <em>%1$s</em> %2$s
                    // translators: %1$s is the ID ad-post, %2$s is the href of edit ad-post link
                    $this->messages[] = [ 'update', wp_kses(sprintf( __( 'New ad created: <em>%1$s</em> %2$s', 'vk-adnetwork' ), $post_id, $link ), ['em' => true, 'a' => ['href' => true]] ) ];
                }

                // new ad id => old ad id, if exists
                $this->imported_data['ads'][ $post_id ] = isset( $ad['ID'] ) ? absint( $ad['ID'] ) : null;

                $post_ids[] = $post_id;

            }
            /** @noinspection PhpUndefinedVariableInspection */
            return $post_ids;
        }
    }

    /**
     * Create new placements based on import information
     *
     * @param array $decoded decoded XML.
     */
    private function import_placements( &$decoded ) {
        if ( isset( $decoded['placements'] ) && is_array( $decoded['placements'] ) ) {

            $existing_placements = $updated_placements = VK_Adnetwork::get_instance()->get_model()->get_ad_placements_array();
            $placement_types = VK_Adnetwork_Placements::get_placement_types();

            foreach ( $decoded['placements'] as &$placement ) {
                $use_existing = ! empty( $placement['use_existing'] );

                // use existing placement
                if ( $use_existing ) {
                    if ( empty( $placement['key'] ) ) {
                        continue;
                    }

                    $placement_key_uniq = sanitize_title( $placement['key'] );
                    if ( ! isset( $existing_placements[ $placement_key_uniq ] ) ) {
                        continue;
                    }

                    $existing_placement = $existing_placements[ $placement_key_uniq ];
                    $existing_placement['key'] = $placement_key_uniq;

                // create new placement
                } else {
                    if ( empty( $placement['key'] ) || empty( $placement['name'] )  || empty( $placement['type'] ) ) {
                        continue;
                    }

                    $placement_key_uniq = sanitize_title( $placement['key']  );
                    if ( $placement_key_uniq === '' ) {
                        continue;
                    }

                    $placement['type'] = ( isset( $placement_types[ $placement['type'] ] ) ) ? $placement['type'] : 'default';
                    $placement['name'] = esc_attr( $placement['name'] );

                    // make sure the key in placement array is unique
                    if ( isset( $existing_placements[ $placement_key_uniq ] ) ) {
                        $count = 1;
                        while ( isset( $existing_placements[ $placement_key_uniq . '_' . $count ] ) ) {
                            $count++;
                        }
                        $placement_key_uniq .= '_' . $count;
                    }

                    // Размещение <em>%s</em> создано
                    // translators: %s is the name of the placement
                    $this->messages[] = [ 'update', wp_kses(sprintf( __( 'Placement <em>%s</em> created', 'vk-adnetwork' ), esc_html( $placement['name'] ) ), ['em' => true] ) ];

                    // new placement key => old placement key
                    $this->imported_data['placements'][ $placement_key_uniq ] = $placement['key'];
                }

                // try to set "Item" (ad or group)
                if ( ! empty( $placement['item'] ) ) {
                    $_item = explode( '_', $placement['item'] );
                    if ( ! empty( $_item[1] ) ) {
                        switch ( $_item[0] ) {
                            case 'ad':
                            case VK_Adnetwork_Select::AD :

                                $found = $this->search_item( $_item[1], VK_Adnetwork_Select::AD );
                                if ( $found === false ) { break; }

                                if ( $use_existing ) {
                                    // assign new ad to an existing placement
                                    // - if the placement has no or a single ad assigned, it will be swapped against the new one
                                    // - if a group is assigned to the placement, the new ad will be added to this group with a weight of 1
                                    $placement = $existing_placement;

                                    if ( ! empty( $placement['item'] ) ) {
                                        // get the item from the existing placement
                                        $_item_existing = explode( '_', $placement['item'] );

                                    }
                                }

                                $placement['item'] = 'ad_' . $found;
                                // new placement key => old placement key
                                $this->imported_data['placements'][ $placement_key_uniq ] = $placement_key_uniq;
                                break;
                        }
                    }
                }

                $updated_placements[ $placement_key_uniq ] = apply_filters( 'vk-adnetwork-import-placement', $placement, $this );
            }

            if ( $existing_placements !== $updated_placements ) {
                VK_Adnetwork::get_instance()->get_model()->update_ad_placements_array( $updated_placements );
            }
        }
    }

    /**
     * Search for ad/group id
     *
     * @param string $id ad/group id
     * @param string $type
     * @return
     * - int id of the imported ad/group if exists
     * - or int id of the existing ad/group if exists
     * - or bool false
     */
    public function search_item( $id, $type ) {
        $found = false;

        switch ( $type ) {
            case 'ad':
            case VK_Adnetwork_Select::AD :
                // if the ad was was imported
                if ( ! $found = array_search( $id, $this->imported_data['ads'] ) ) {
                    // if the ad already exists
                    if ( get_post_type( $id ) === VK_Adnetwork::POST_TYPE_SLUG ) {
                        $found = $id;
                    }
                }
                break;
        }

        return (int) $found;
    }

    /**
     * Create new options based on import information.
     *
     * @param array $decoded decoded XML.
     */
    private function import_options( &$decoded ) {
        if ( isset( $decoded['options'] ) && is_array( $decoded['options'] ) ) {
            foreach ( $decoded['options'] as $option_name => $imported_option ) {
                // Ignore options not belonging to vk adnetwork.
                if (
                    !str_starts_with($option_name, 'vk_adnetwork-')
                    && !str_starts_with($option_name, 'vk_adnetwork_')
                    && !str_starts_with($option_name, 'vk-adnetwork')
                    && !str_starts_with($option_name, 'vk_adnetwork')
                ) {
                    continue;
                }

                $existing_option = get_option( $option_name, [] );

                if ( ! is_array( $imported_option ) ) {
                    $imported_option = [];
                }
                if ( ! is_array( $existing_option ) ) {
                    $existing_option = [];
                }

                $option_to_import = array_merge( $existing_option, $imported_option );

                /* translators: %s: Option name. */
                $this->messages[] = [ 'update', wp_kses(sprintf( __( 'Option was updated: <em>%s</em>', 'vk-adnetwork' ), $option_name ), ['em' => true] ) ];
                update_option( $option_name, maybe_unserialize( $option_to_import ) );
            }
        }
    }

    /**
     * Handles the XML upload
     *
     * @return bool false if error, true otherwise
     */
    private function handle_upload() {
        $uploads_dir = wp_upload_dir();
        if ( ! empty( $uploads_dir['error'] ) ) {
            $this->messages[] = [ 'error', $uploads_dir['error'] ];
            return;
        }

        if ( ! isset( $_POST['_wpnonce'] )
            || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'vk_adnetwork-import' )
            || ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options') ) ) {
            return;
        }elseif ( ! isset( $_FILES['import'] ) ) {
            $this->messages[] = [ 'error', esc_html__( 'File is empty, uploads are disabled or post_max_size is smaller than upload_max_filesize in php.ini', 'vk-adnetwork' ) ];
            return;
        }

        // instead VK_Adnetwork_Utils::vk_adnetwork_sanitize_array()
        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $keyfile) {
            if ($_FILES['import'][$keyfile])
                $file[$keyfile] = sanitize_text_field($_FILES['import'][$keyfile]);
        }

        // determine if uploaded file exceeds space quota.
        $file = apply_filters( "wp_handle_upload_prefilter", $file );

        if ( ! empty( $file['error'] ) ) {
            // Не удалось загрузить файл, ошибка: <em>%s</em>
            // translators: %s is an error message
            $this->messages[] = [ 'error', wp_kses(sprintf( __( 'Failed to upload file, error: <em>%s</em>', 'vk-adnetwork' ), $file['error'] ), ['em' => true] ) ];
            return;
        }

        if ( ! ( $file['size'] > 0 ) ) {
            $this->messages[] = [ 'error', esc_html__( 'File is empty.', 'vk-adnetwork' ), $file['error'] ];
            return;
        }

        // вот эта вот залепуха ('xml' => 'text/html') сработает ТОЛЬКО если в xml-файле для импорта удалить
        // первую строку -- «?xml version="1.0" encoding="UTF-8"?» -- без неё файл нормально закачается
        // система распознает его как text/html. А файлы типа text/xml -- в вордпресс закачивать просто НЕЛЬЗЯ
        // (нет такого типа в списке разрешенных mime-типов -- см. wp_get_mime_types())
        $movefile = wp_handle_upload( $file, [ 'test_form' => false, 'mimes' => [ 'xml' => 'text/html' ] ] );
        if( ! $movefile || ! empty($movefile['error']) ) {
            // Невозможно создать файл: <em>%s</em>. Возможна проблема с правами доступа.
            // translators: %s is the name of the temporary file
            $this->messages[] = [ 'error', wp_kses(sprintf( __( 'The file could not be created: <em>%s</em>. This is probably a permissions problem', 'vk-adnetwork' ), $this->import_id ), ['em' => true] ) ];
            return;
        }
        $this->import_id = $movefile['file'];

        // cleanup in case of failed import
        wp_schedule_single_event( time() + 10 * MINUTE_IN_SECONDS, 'vk-adnetwork-cleanup-import-file', [ $this->import_id ] );

        return true;
    }

    /**
     * Ad content manipulations
     *
     * @param string $content
     * @return string $content
     */
    private function process_ad_content( $content ) {

        // replace placeholders
        return $this->replace_placeholders( $content );
    }

    /**
     * Replace placeholders
     *
     * @param string $content
     * @return string with replaced placeholders
     */
    private function replace_placeholders( $content ) {
        $content = str_replace( '{VK_ADNETWORK_BASE_URL}', VK_ADNETWORK_BASE_URL, $content );
        return $content;
    }

    public function get_messages(){
        return $this->messages;
    }
}
