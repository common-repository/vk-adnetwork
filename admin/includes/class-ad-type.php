<?php

/**
 * Class VK_Adnetwork_Admin_Ad_Type
 */
class VK_Adnetwork_Admin_Ad_Type {
    /**
     * Instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Post type slug
     *
     * @since   1.0.0
     * @var     string
     */
    protected $post_type = '';

    /**
     * Register hooks function related to the ad type
     */
    private function __construct() {
        // Register column headers.
        add_filter('manage_vk_adnetwork_posts_columns', [$this, 'ad_list_columns_head',]);
        add_filter( 'manage_vk_adnetwork_posts_custom_column', [ $this, 'ad_list_columns' ], 10, 2 );
        // Add custom filter views.
        add_action( 'restrict_manage_posts', [ $this, 'ad_list_add_filters' ] );
        add_filter( 'default_hidden_columns', [ $this, 'hide_ad_list_columns' ], 10, 2 );
        add_filter( 'bulk_post_updated_messages', [ $this, 'ad_bulk_update_messages' ], 10, 2 );
        // order ad lists.
        add_filter( 'request', [ $this, 'ad_list_request' ] );
        // Manipulate post data when post is created. // дефолтный заголовок если пустой: Ad created on <дата-время>
        add_filter( 'wp_insert_post_data', [ $this, 'prepare_insert_post_data' ] );
        // Save ads post type.
        // @source https://developer.wordpress.org/reference/hooks/save_post_post-post_type/
        add_action( 'save_post_vk_adnetwork', [ $this, 'save_ad' ] );
        //add_action( 'delete_post', [ $this, 'delete_ad' ] );
        add_action( 'edit_form_top', [ $this, 'edit_form_above_title' ] );
        add_action( 'post_submitbox_misc_actions', [ $this, 'add_submit_box_meta' ] );
        add_filter( 'post_updated_messages', [ $this, 'ad_update_messages' ] );
        add_filter( 'gettext', [ $this, 'replace_cheating_message' ], 20, 2 );
        add_action( 'current_screen', [ $this, 'run_on_ad_edit_screen' ] );
        add_filter( 'pre_wp_unique_post_slug', [ $this, 'pre_wp_unique_post_slug' ], 10, 6 );
        add_filter( 'view_mode_post_types', [ $this, 'remove_view_mode' ] );
        add_filter( 'get_user_option_user-settings', [ $this, 'reset_view_mode_option' ] );
        add_filter( 'screen_settings', [ $this, 'add_screen_options' ], 10, 2 );
        add_action( 'wp_loaded', [ $this, 'save_screen_options' ] );
        add_action( 'load-edit.php', [ $this, 'set_screen_options' ] );

        add_filter( 'post_row_actions', [ $this, 'remove_inline_edit' ], 10, 2 );

        add_filter( 'safe_style_css', function( $styles ) { $styles[] = 'display'; return $styles; } );

        $this->post_type = constant( 'VK_Adnetwork::POST_TYPE_SLUG' );
    }

    /*
     * убираем | Свойства | в в доп-меню в списке Объявлений
     * т.е. там в списке: Изменить | Свойства | Удалить -- остается только: Изменить | Удалить
     * Свойства - стрёмные! Там какой-то ПАРОЛЬ ненужный в рекламе или ЛИЧНОЕ
     * плюс сохранение свойств ломает объявление (удаляет Заметки и код рекламы, ХЗ почему)
     */
    public function remove_inline_edit( $actions, $post ) {
        unset($actions['inline hide-if-no-js']); // вот такое жудкое название у этих Свойств
        return $actions;
    }

    /**
     * Return an instance of this class.
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {
        // If the single instance hasn't been set, set it now.
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add heading for extra column of ads list
     * remove the date column
     *
     * @param string[] $columns array with existing columns.
     *
     * @return string[]
     */
    public function ad_list_columns_head( $columns ) {
        $new_columns = [];

        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            // add ad icon column after the checkbox
            if ( $key === 'cb' ) {
                // $new_columns['ad_type'] = esc_html__( 'Type', 'vk-adnetwork' );
                continue;
            }

            if ( $key === 'title' ) {
                $new_columns['ad_slot_id']     = esc_html__( 'Block',           'vk-adnetwork' ); // [+] id of pad in VK AdNetwork
                $new_columns['title']          = esc_html__( 'Ad blocks',       'vk-adnetwork' );
                $new_columns['ad_status']      = esc_html__( 'Статус',          'vk-adnetwork' );
                $new_columns['ad_description'] = esc_html__( 'Notes',           'vk-adnetwork' );
                $new_columns['ad_size']        = esc_html__( 'Size',            'vk-adnetwork' );  // [+] 900x250, etc
                $new_columns['ad_shortcode']   = esc_html__( 'Ad Shortcode',    'vk-adnetwork' );  // [+] [vk_adnetwork_the_ad id="..."]
                $new_columns['ad_placement']   = esc_html__( 'Placement',       'vk-adnetwork' );  // [+] post_top, post_bottom
                $new_columns['ad_timing']      = esc_html__( 'Ads planning',    'vk-adnetwork' );  // [+] Планировщик объявлений//////

              //$new_columns['ad_preview']     = esc_html__( 'Preview',         'vk-adnetwork' );  // [-] XZ: Предпросмотр
            }
        }

        $allowed_columns = [
            'cb', // checkbox.
            'title',
            'author',
            // 'ad_type',
            'ad_description',
            'ad_preview',
            'ad_status',
            'ad_size',
            'ad_timing',
            'ad_shortcode',
            'ad_placement',
            'ad_slot_id',
        ];

        /**
         * Filter the allowed columns for VK AdNetwork post type list.
         *
         * @param string[] $allowed_columns The allowed column names.
         */
        $allowed_columns = (array) apply_filters( 'vk-adnetwork-ad-list-allowed-columns', $allowed_columns );

        return array_intersect_key( $new_columns, array_flip( $allowed_columns ) );
    }

    /**
     * Add ad list column content
     *
     * @param string $column_name name of the column.
     * @param int    $ad_id id of the ad.
     *
     * @return void
     */
    public function ad_list_columns( $column_name, $ad_id ) {
        $ad = new VK_Adnetwork_Ad( $ad_id );

        switch ( $column_name ) {

            case 'ad_description':
                $this->ad_list_columns_description( $ad );
                break;
            case 'ad_preview':
                $this->ad_list_columns_preview( $ad );
                break;
            case 'ad_status':
                $this->ad_list_columns_status($ad);
                break;
            case 'ad_size':
                $this->ad_list_columns_size( $ad );
                break;
            case 'ad_timing':
                $this->ad_list_columns_timing( $ad );
                break;
            case 'ad_shortcode':
                $this->ad_list_columns_shortcode( $ad );
                break;
            case 'ad_placement':
                $this->ad_list_columns_placement( $ad );
                break;
            case 'ad_slot_id':
                $this->ad_list_columns_slot_id( $ad );
                break;
        }
    }

    /**
     * Display the ad description in the ads list
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     *
     * @return void
     */
    private function ad_list_columns_description( VK_Adnetwork_Ad $ad ) {
        $description = wp_trim_words( $ad->description, 50 );

        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list/description.php';
    }

    /**
     * Display an ad preview in ads list.
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     *
     * @return void
     */
    private function ad_list_columns_preview( $ad ) {
        $types = VK_Adnetwork::get_instance()->ad_types;
        $type  = ( ! empty( $types[ $ad->type ]->title ) ) ? $types[ $ad->type ]->title : 0;

        if ( ! $type ) {
            return;
        }

        if ( ! empty( $type ) ) {
            $types[ $ad->type ]->render_preview( $ad );
        }

        do_action( 'vk-adnetwork-ad-list-details-column-after', $ad );
    }

    /**
     * Display the ad size in the ads list
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     *
     * @return void
     */
    private function ad_list_columns_size( $ad ) {
        $size = $this->get_ad_size_string( $ad );

        if ( empty( $size ) ) {
            return;
        }

        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list/size.php';
    }

        /**
     * Display the ad status in the ads list
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     *
     * @return void
     */
    private function ad_list_columns_status( $ad ) {
        $status = get_post_status($ad->id);

        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list/status.php';
    }

    /**
     * Display ad timing in ads list
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     *
     * @return void
     */
    public function ad_list_columns_timing( $ad ) {
        $expiry             = false;
        $post_future        = false;
        $post_start         = get_post_time( 'U', true, $ad->id );
        $html_classes       = 'vk_adnetwork-filter-timing';
        $expiry_date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );

        if ( isset( $ad->expiry_date ) && $ad->expiry_date ) {
            $html_classes .= ' vk_adnetwork-filter-any-exp-date';

            $expiry = $ad->expiry_date;
            if ( $ad->expiry_date < time() ) {
                $html_classes .= ' vk_adnetwork-filter-expired';
            }
        }
        if ( $post_start > time() ) {
            $post_future   = $post_start;
            $html_classes .= ' vk_adnetwork-filter-future';
        }

        ob_start();
        do_action_ref_array(
            'vk-adnetwork-ad-list-timing-column-after',
            [
                $ad,
                &$html_classes,
            ]
        );
        $content_after = ob_get_clean();

        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list/timing.php';
    }

    /**
     * Display ad shortcode in ads list
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     *
     * @return void
     */
    public function ad_list_columns_shortcode( $ad ) {
        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list/shortcode.php';
    }

    public function ad_list_columns_placement( $ad ) {
        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list/placement.php';
    }

    public function ad_list_columns_slot_id( $ad ) {
        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list/slot_id.php';
    }

    /**
     * Hide certain columns on the ad list by default.
     *
     * @param array     $hidden an array of columns hidden by default.
     * @param WP_Screen $screen WP_Screen object of the current screen.
     *
     * @return array
     */
    public function hide_ad_list_columns( $hidden, $screen ) {
        if ( isset( $screen->id ) && 'edit-' . VK_Adnetwork::POST_TYPE_SLUG === $screen->id ) {
            $hidden[] = 'author';           // [-] Автор vkadsteam
            $hidden[] = 'ad_preview';       // [-] XZ: Preview     = Предпросмотр
            // $hidden[] = 'ad_timing';      // [-] XZ: Ads planning = Планировщик объявлений
            // $hidden[] = 'ad_description'; // [?] Notes
            // $hidden[] = 'ad_size';        // [+] 900x250, etc
            // $hidden[] = 'ad_shortcode';   // [+] [vk_adnetwork_the_ad id="..."]
        }

        return $hidden;
    }

    /**
     * Adds filter dropdowns before the 'Filter' button on the ad list table
     *
     * @return void
     */
    public function ad_list_add_filters() {
        $screen = get_current_screen();
        if ( ! isset( $screen->id ) || 'edit-vk_adnetwork' !== $screen->id ) { // ads overview. /wp-admin/edit.php?post_type=vk_adnetwork
            return;
        }
        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list-filters.php';
    }

    /**
     * Edit ad bulk update messages
     *
     * @param array $messages existing bulk update messages.
     * @param array $counts numbers of updated ads.
     *
     * @return array
     *
     * @see wp-admin/edit.php
     */
    public function ad_bulk_update_messages( array $messages, array $counts ) {
        $post = get_post();

        $messages[ VK_Adnetwork::POST_TYPE_SLUG ] = [
            // translators: %s is the number of ads.
            'updated'   => _n( '%s ad updated.', '%s ads updated.', $counts['updated'], 'vk-adnetwork' ),
            // translators: %s is the number of ads.
            'locked'    => _n( '%s ad not updated, somebody is editing it.', '%s ads not updated, somebody is editing them.', $counts['locked'], 'vk-adnetwork' ),
            // translators: %s is the number of ads.
            'deleted'   => _n( '%s ad permanently deleted.', '%s ads permanently deleted.', $counts['deleted'], 'vk-adnetwork' ),
            // translators: %s is the number of ads.
            'trashed'   => _n( '%s ad moved to the Trash.', '%s ads moved to the Trash.', $counts['trashed'], 'vk-adnetwork' ),
            // translators: %s is the number of ads.
            'untrashed' => _n( '%s ad restored from the Trash.', '%s ads restored from the Trash.', $counts['untrashed'], 'vk-adnetwork' ),
        ];

        return $messages;
    }

    /**
     * Order ads by title on ads list
     *
     * @param array $vars array with request vars.
     *
     * @return array
     */
    public function ad_list_request( $vars ) {
        // if we shouldn't filter this return $vars array.
        if (
            ! isset( $vars['post_type'] )
            || $vars['post_type'] !== VK_Adnetwork::POST_TYPE_SLUG
            || ! is_admin()
            || wp_doing_ajax()
        ) {
            return $vars;
        }

        // order ads by title on ads list by default
        if ( empty( $vars['orderby'] ) ) {
            add_action( 'pre_get_posts', [ $this, 'default_ad_list_order' ] );
        }

        if ( $vars['orderby'] === 'expiry_date' ) {
            $vars['orderby']  = 'meta_value';
            $vars['meta_key'] = VK_Adnetwork_Ad_Expiration::POST_META;      // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            $vars['order']    = strtoupper( $vars['order'] ) === 'DESC' ? 'DESC' : 'ASC';

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( isset( $_GET['addate'] ) && $_GET['addate'] === 'vk_adnetwork-filter-expired' ) {
                $vars['post_status'] = VK_Adnetwork_Ad_Expiration::POST_STATUS;
            }
        }

        return $vars;
    }

    /**
     * Set default ad list order.
     *
     * @param WP_Query $query The current WP_Query, passed by reference.
     *
     * @return void
     */
    public function default_ad_list_order( WP_Query $query ) {
        if ( ! $query->is_main_query() ) {
            return;
        }

        $query->set( 'orderby', 'title' );
        $query->set( 'order', 'ASC' );
    }

    /**
     * Prepare the ad post type to be saved
     *
     * @param int $post_id id of the post.
     * @todo handling this more dynamic based on ad type
     */
    public function save_ad( $post_id ) {
        if ( !$_POST
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vk_adnetwork_ad_post_form_nonce'] ?? '')), 'vk_adnetwork_ad_post_form')
            || ! check_admin_referer("vk_adnetwork-save-ad-$post_id", 'vk_adnetwork_save_nonce')
            || ! current_user_can(VK_Adnetwork_Plugin::user_cap('vk_adnetwork_edit_ads'))
            || ! isset($_POST['post_type'])
            || $this->post_type !== $_POST['post_type']
            || wp_is_post_revision( $post_id )
        ) {
            return;
        }

        $_POST['vk_adnetwork']['type'] = 'plain'; // -x- 'ad-main-box', плашка Тип объявлений

        // get ad object.
        $ad = new VK_Adnetwork_Ad( $post_id );
        if ( ! $ad instanceof VK_Adnetwork_Ad ) {
            return;
        }

        $ad->type = 'plain'; // wp_unslash( $_POST['vk_adnetwork']['type'] );

        $ad->url = 0;
        if ( isset( $_POST['vk_adnetwork']['url'] ) ) {
            // May contain placeholders added by the tracking add-on.
            $ad->url = sanitize_url( trim($_POST['vk_adnetwork']['url']) );
        }

        // save size.
        $ad->width = 0;
        if ( isset( $_POST['vk_adnetwork']['width'] ) ) {
            $ad->width = absint( $_POST['vk_adnetwork']['width'] );
        }
        $ad->height = 0;
        if ( isset( $_POST['vk_adnetwork']['height'] ) ) {
            $ad->height = absint( $_POST['vk_adnetwork']['height'] );
        }
        // format_id
        if ( isset( $_POST['vk_adnetwork']['format_id'] ) ) {
            $ad->format_id = absint( $_POST['vk_adnetwork']['format_id'] );
        }

        if ( ! empty( $_POST['vk_adnetwork']['description'] ) ) {
            $ad->description = sanitize_textarea_field($_POST['vk_adnetwork']['description']);
        } else {
            $ad->description = '';
        }

        if ( ! empty( $_POST['vk_adnetwork']['content'] ) ) {
            $ad->content = wp_kses( $_POST['vk_adnetwork']['content'], wp_kses_allowed_html( 'post' ) + [ 'script' => true ] );
        } else {
            $ad->content = '';
        }

        $output = []; // instead VK_Adnetwork_Utils::vk_adnetwork_sanitize_array()
        if ( ! empty( $_POST['vk_adnetwork']['output'] ) && is_array( $_POST['vk_adnetwork']['output'] ) ) {
            foreach (['debugmode', 'mtdebugmode', 'allow_shortcodes'] as $keyoutput) {
                if (isset($_POST['vk_adnetwork']['output'][$keyoutput]))
                    $output[$keyoutput] = absint($_POST['vk_adnetwork']['output'][$keyoutput]);
            }
        }
        if ( ! empty( $_POST['vk_adnetwork']['output']['padding'] ) && is_array( $_POST['vk_adnetwork']['output']['padding'] ) ) {
            foreach (['top', 'left', 'right', 'bottom'] as $keypadding) {
                if (isset($_POST['vk_adnetwork']['output']['padding'][$keypadding]))
                    $output['padding'][$keypadding] = absint($_POST['vk_adnetwork']['output']['padding'][$keypadding]);
            }
        }

        // Set output.
        $ad->set_option( 'output', $output );

        // prepare expiry date.
        if ( isset( $_POST['vk_adnetwork']['expiry_date']['enabled'] ) ) {
            $year   = absint( $_POST['vk_adnetwork']['expiry_date']['year'] );
            $month  = absint( $_POST['vk_adnetwork']['expiry_date']['month'] );
            $day    = absint( $_POST['vk_adnetwork']['expiry_date']['day'] );
            $hour   = absint( $_POST['vk_adnetwork']['expiry_date']['hour'] );
            $minute = absint( $_POST['vk_adnetwork']['expiry_date']['minute'] );

            $expiration_date = sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, '00' );
            $valid_date      = wp_checkdate( $month, $day, $year, $expiration_date );

            if ( ! $valid_date ) {
                $ad->expiry_date = 0;
            } else {
                $gm_date = date_create( $expiration_date, VK_Adnetwork_Utils::vk_adnetwork_get_wp_timezone() );
                $gm_date->setTimezone( new DateTimeZone( 'UTC' ) );
                $gm_date                                    = $gm_date->format( 'Y-m-d-H-i' );
                [ $year, $month, $day, $hour, $minute ] = explode( '-', $gm_date );
                $ad->expiry_date                            = gmmktime( $hour, $minute, 0, $month, $day, $year );
            }
        } else {
            $ad->expiry_date = 0;
        }

        $ad->save();
    }

    /**
     * Prepare main post data for ads when being saved.
     *
     * Set default title if it is empty.
     *
     * @param array $data An array of slashed post data.
     * @return array
     */
    public static function prepare_insert_post_data( $data ) {
        if ( VK_Adnetwork::POST_TYPE_SLUG === $data['post_type']
            && '' === $data['post_title'] ) {
            if ( function_exists( 'wp_date' ) ) {
                // The function wp_date was added in WP 5.3.
                $created_time = wp_date( get_option( 'date_format' ) ) . ' ' . wp_date( get_option( 'time_format' ) );
            } else {
                // Just attach the post date raw form.
                $created_time = $data['post_date'];
            }

            // Create timestamp from current data.
            $data['post_title'] = sprintf(
            // Translators: %s is the time the ad was first saved.
                esc_html__( 'сreated on %s', 'vk-adnetwork' ),
                $created_time
            );
        }

        return $data;
    }

    /**
     * Add information above the ad title
     *
     * @param object $post WordPress post type object.
     *
     * @since 1.5.6
     */
    public function edit_form_above_title( $post ) { }

    /**
     * Add meta values below submit box
     *
     * @since 1.3.15
     */
    public function add_submit_box_meta() {
        global $post, $wp_locale;

        if ( VK_Adnetwork::POST_TYPE_SLUG !== $post->post_type ) {
            return;
        }

        $ad = new VK_Adnetwork_Ad( $post->ID );

        // get time set for ad or current timestamp (both GMT).
        $utc_ts    = $ad->expiry_date ?: time();
        $utc_time  = date_create( '@' . $utc_ts );
        $tz_option = get_option( 'timezone_string' );
        $exp_time  = clone $utc_time;

        if ( $tz_option ) {
            $exp_time->setTimezone( VK_Adnetwork_Utils::vk_adnetwork_get_wp_timezone() );
        } else {
            $tz_name       = VK_Adnetwork_Utils::vk_adnetwork_get_timezone_name();
            $tz_offset     = substr( $tz_name, 3 );
            $off_time      = date_create( $utc_time->format( 'Y-m-d\TH:i:s' ) . $tz_offset );
            $offset_in_sec = date_offset_get( $off_time );
            $exp_time      = date_create( '@' . ( $utc_ts + $offset_in_sec ) );
        }

        [ $curr_year, $curr_month, $curr_day, $curr_hour, $curr_minute ] = explode( '-', $exp_time->format( 'Y-m-d-H-i' ) );
        $enabled = 1 - empty( $ad->expiry_date );

        include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-submitbox-meta.php';
    }

    /**
     * Edit ad update messages
     *
     * @param array $messages existing post update messages.
     *
     * @return array $messages
     *
     * @since 1.4.7
     * @see wp-admin/edit-form-advanced.php
     */
    public function ad_update_messages( $messages = [] ) {
        $post = get_post();

        // added to hide error message caused by third party code that uses post_updated_messages filter wrong.
        if ( ! is_array( $messages ) ) {
            return $messages;
        }

        $messages[ VK_Adnetwork::POST_TYPE_SLUG ] = [
            0  => '', // Unused. Messages start at index 1.
            1  => esc_html__( 'Ad updated.', 'vk-adnetwork' ),
            4  => esc_html__( 'Ad updated.', 'vk-adnetwork' ), /* translators: %s: date and time of the revision */
            // translators: %s is the formatted date timestamp of a revision
            5  => isset( $_GET['revision'] ) ? sprintf( esc_html__( 'Ad restored to revision from %s', 'vk-adnetwork' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            6  => esc_html__( 'Ad saved.', 'vk-adnetwork' ), // published.
            7  => esc_html__( 'Ad saved.', 'vk-adnetwork' ), // saved.
            8  => esc_html__( 'Ad submitted.', 'vk-adnetwork' ),
            9  => sprintf(
            // translators: %1$s is a date.
                wp_kses( __( 'Ad scheduled for: <strong>%1$s</strong>.', 'vk-adnetwork' ), ['strong' => true]),
                    // translators: Publish box date format, see http://php.net/date.
                    date_i18n( esc_html__( 'M j, Y @ G:i', 'vk-adnetwork' ), strtotime( $post->post_date ) )
            ),
            10 => esc_html__( 'Ad draft updated.', 'vk-adnetwork' ),
        ];

        return $messages;
    }

    /**
     * Replace 'You need a higher level of permission.' message if user role does not have required permissions.
     *
     * @param string $translated_text Translated text.
     * @param string $untranslated_text Text to translate.
     *
     * @return string $translation  Translated text.
     */
    public function replace_cheating_message( $translated_text, $untranslated_text ) {
        global $typenow;

        if ( isset( $typenow ) && 'You need a higher level of permission.' === $untranslated_text && $typenow === $this->post_type ) {
            $translated_text = esc_html__( 'You don’t have access to ads. Please deactivate and re-enable VK AdNetwork again to fix this.', 'vk-adnetwork' );
        }

        return $translated_text;
    }

    /**
     * General stuff after ad edit page is loaded and screen variable is available
     */
    public function run_on_ad_edit_screen() { }

    /**
     * Create a unique across all post types slug for the ad.
     * Almost all code here copied from `wp_unique_post_slug()`.
     *
     * @param string $override_slug Short-circuit return value.
     * @param string $slug The desired slug (post_name).
     * @param int    $post_ID Post ID.
     * @param string $post_status The post status.
     * @param string $post_type Post type.
     * @param int    $post_parent Post parent ID.
     *
     * @return string
     */
    public function pre_wp_unique_post_slug( $override_slug, $slug, $post_ID, $post_status, $post_type, $post_parent ) {
        if ( VK_Adnetwork::POST_TYPE_SLUG !== $post_type ) {
            return $override_slug;
        }

        global $wpdb, $wp_rewrite;

        $feeds = $wp_rewrite->feeds;
        if ( ! is_array( $feeds ) ) {
            $feeds = [];
        }

        // VK AdNetwork post types slugs must be unique across all types.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $post_name_check = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND ID != %d LIMIT 1", $slug, $post_ID ) );

        if ( $post_name_check || in_array( $slug, $feeds, true ) || 'embed' === $slug ) {
            $suffix = 2;
            do {
                $alt_post_name   = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $post_name_check = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND ID != %d LIMIT 1", $alt_post_name, $post_ID ) );
                $suffix ++;
            } while ( $post_name_check );
            $override_slug = $alt_post_name;
        }

        return $override_slug;
    }

    /**
     * Remove the View Mode setting in Screen Options
     *
     * @param array $view_mode_post_types post types that have the View Mode option.
     *
     * @return array
     */
    public function remove_view_mode( $view_mode_post_types ) {
        unset( $view_mode_post_types['vk_adnetwork'] );

        return $view_mode_post_types;
    }

    /**
     * Set the removed post list mode to "List", if it was set to "Excerpt".
     *
     * @param string $user_options Query string containing user options.
     *
     * @return string
     */
    public function reset_view_mode_option( $user_options ) {
        return str_replace( '&posts_list_mode=excerpt', '&posts_list_mode=list', $user_options );
    }

    /**
     * Register custom screen options on the ad overview page.
     *
     * @param string    $options Screen options HTML.
     * @param WP_Screen $screen  Screen object.
     *
     * @return string
     */
    public function add_screen_options( $options, WP_Screen $screen ) {
        if ( $screen->base !== 'edit' || $screen->id !== 'edit-vk_adnetwork' ) { // ads overview. /wp-admin/edit.php?post_type=vk_adnetwork
            return $options;
        }

        $show_filters = (bool) $screen->get_option( 'show-filters' );

        // If the default WordPress screen options don't exist, we have to force the submit button to show.
        add_filter( 'screen_options_show_submit', '__return_true' );
        ob_start();
        require VK_ADNETWORK_BASE_PATH . 'admin/views/ad-list/screen-options.php';

        return $options . ob_get_clean();
    }

    /**
     * Save the screen option setting.
     *
     * @return void
     */
    public function save_screen_options() {
        if ( ! isset( $_POST['vk-adnetwork-screen-options'] ) || ! is_array( $_POST['vk-adnetwork-screen-options'] ) ) {
            return;
        }

        check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' );

        $user = wp_get_current_user();

        if ( ! $user ) {
            return;
        }

        // sanitize options
        update_user_meta( $user->ID, 'vk-adnetwork-ad-list-screen-options', [
            'show-filters' => ! empty( $_POST['vk-adnetwork-screen-options']['show-filters'] ),
        ] );
    }

    /**
     * Add the screen options to the WP_Screen options
     *
     * @return void
     */
    public function set_screen_options() {
        $screen = get_current_screen();

        if ( ! isset( $screen->id ) || $screen->id !== 'edit-vk_adnetwork' ) { // ads overview. /wp-admin/edit.php?post_type=vk_adnetwork
            return;
        }

        $screen_options = get_user_meta( get_current_user_id(), 'vk-adnetwork-ad-list-screen-options', true );
        if ( ! is_array( $screen_options ) ) {
            return;
        }
        foreach ( $screen_options as $option_name => $value ) {
            add_screen_option( $option_name, $value );
        }
    }

    /**
     * Get the ad size string to display in post list.
     *
     * @param VK_Adnetwork_Ad $ad Ad object.
     *
     * @return string
     */
    private function get_ad_size_string( VK_Adnetwork_Ad $ad ) {
        // load ad size.
        $size = '';
        if ( ! empty( $ad->width ) || ! empty( $ad->height ) ) {
            $size = esc_html(sprintf( '%d &times; %d', $ad->width, $ad->height )); // &times; &#215; × знак умножения
        }

        /**
         * Filter the ad size string to display in the ads post list.
         *
         * @param string          $size Size string.
         * @param VK_Adnetwork_Ad $ad   Ad object.
         */
        return (string) apply_filters( 'vk-adnetwork-list-ad-size', $size, $ad );
    }
}
