<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class VK_Adnetwork_Admin_Meta_Boxes
 */
class VK_Adnetwork_Admin_Meta_Boxes {
    /**
     * Instance of this class.
     *
     * @var      object $instance
     */
    protected static $instance = null;

    /**
     * Meta box ids
     *
     * @var     array $meta_box_ids
     */
    protected $meta_box_ids = [];

    /**
     * VK_Adnetwork_Admin_Meta_Boxes constructor.
     */
    private function __construct() { // add_meta_boxes_vk_adnetwork
        add_action( 'add_meta_boxes_' . VK_Adnetwork::POST_TYPE_SLUG, [ $this, 'add_meta_boxes' ] );
        // add meta box for post types edit pages.
        add_action( 'add_meta_boxes', [ $this, 'add_post_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_post_meta_box' ] );
        // register dashboard widget.
        add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
        // fixes compatibility issue with WP QUADS PRO.
        add_action( 'quads_meta_box_post_types', [ $this, 'fix_wpquadspro_issue' ], 11 );
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
     * Add meta boxes
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        global $post;
        $post_type = VK_Adnetwork::POST_TYPE_SLUG;

        // use dynamic filter from to add close class to ad type meta box after saved first time.
        // ??? используйте динамический фильтр from, чтобы добавить мета-поле close class road type после сохранения в первый раз.
        add_filter( 'postbox_classes_vk_adnetwork_ad-main-box', [ $this, 'close_ad_type_metabox' ] );

        // show the Usage box for saved ads
        //if ( $post->filter === 'edit' ) {
            add_meta_box(
                'ad-usage-box',
                esc_html__( 'Notes (optional)', 'vk-adnetwork' ),          // плашка Применение            // Заметки § Шорткод § Шаблон
                [ $this, 'markup_meta_boxes' ],
                $post_type,
                'normal',
                'high'
            );
        //}
        add_meta_box(
            'ad-parameters-box',
            esc_html__( 'Ad Parameters', 'vk-adnetwork' ),      // плашка Параметры объявлений  // -x- Вставьте простой текст или код в это поле. § Разрешить PHP § Выполнить шорткоды § Размер ширина 300 px высота 600 px § зарезервировать это пространство
            [ $this, 'markup_meta_boxes' ],
            $post_type,
            'normal',
            'high'
        );

        add_meta_box(
            'ad-verticalpositioning-box',
            esc_html__( 'Align block vertically', 'vk-adnetwork' ),      // плашка Выравнивание блока по вертикали  // Над / Внутри / Под / Шорткод
            [ $this, 'markup_meta_boxes' ],
            $post_type,
            'normal',
            'high'
        );
        add_meta_box(
            'ad-output-box',
            esc_html__( 'Debug mode', 'vk-adnetwork' ),    // плашка Режим отладки // Включить режим отладки (wordpress) Руководство § Включить режим отладки (VK AdNetwork)
            [ $this, 'markup_meta_boxes' ],
            $post_type,
            'normal',
            'high'
        );

        // register meta box ids.
        $this->meta_box_ids = [
            'ad-verticalpositioning-box', // плашка Выравнивание блока по вертикали // Над / Внутри / Под / Шорткод
            'ad-parameters-box',    // плашка Параметры объявлений                  // Размер ширина 300 px высота 600 px § зарезервировать это пространство
            'ad-output-box',        // плашка Режим отладки                         // Включить режим отладки (wordpress) Руководство § Включить режим отладки (VK AdNetwork)
        ];

        // force AA meta boxes to never be completely hidden by screen options.
        add_filter( 'hidden_meta_boxes', [ $this, 'unhide_meta_boxes' ], 10, 2 );
        // hide the checkboxes for "unhideable" meta boxes within screen options via CSS.
        add_action( 'admin_head', [ $this, 'unhide_meta_boxes_style' ] );

        $whitelist = apply_filters(
            'vk-adnetwork-ad-edit-allowed-metaboxes',
            array_merge(
                $this->meta_box_ids,
                [ // meta boxes in this array can be hidden using Screen Option
                    'submitdiv',            // Опубликовать                                     // Статус: Опубликовано § Опубликовано в: § Указать срок годности § Удалить
                    'ad-usage-box',         // плашка Применение                                // Заметки § Шорткод § Шаблон
                    // 'slugdiv',           // WP: add_meta_box( 'slugdiv', __( 'Slug' ), ... XZ ( ~ Ярлык
                    // 'authordiv',         // WP: add_meta_box( 'authordiv', __( 'Author' ), ...
                    // 'tracking-ads-box',  // ??? м.б. это? Трекинг Вести Статистику Показов И Кликов По Объявлениям Сравнивать Объявления И Периоды Поделиться Отчетами Через Ссылку Или Отправить На Email Ограничить Показы Объявлений По Суммарному Количеству Показов Или Кликов Распределять Показы Или Клики Равномерно По Заданному Периоду
                    // 'ad-layer-ads-box',  // deprecated.
                ]
            )
        );

        global $wp_meta_boxes;
        // remove non-white-listed meta boxes.
        foreach ( [ 'normal', 'advanced', 'side' ] as $context ) {
            if ( isset( $wp_meta_boxes[ $post_type ][ $context ] ) ) {
                foreach ( [ 'high', 'sorted', 'core', 'default', 'low' ] as $priority ) {
                    if ( isset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ] ) ) {
                        foreach ( (array) $wp_meta_boxes[ $post_type ][ $context ][ $priority ] as $id => $box ) {
                            if ( ! in_array( $id, $whitelist ) ) {
                                unset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ][ $id ] );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Load templates for all meta boxes
     *
     * @param WP_Post $post WP_Post object.
     * @param array   $box  meta box information.
     * @todo move ad initialization to main function and just global it
     */
    public function markup_meta_boxes( $post, $box ) {
        $ad = new VK_Adnetwork_Ad( $post->ID );

        switch ( $box['id'] ) {
            case 'ad-verticalpositioning-box':                    // плашка Выравнивание блока по вертикали // Над / Внутри / Под / Шорткод
                $placements      = VK_Adnetwork::get_ad_placements_array(); // -TODO use model
                // выделим местоположение ($placement['type'] = post_top, post_content, post_bottom, default), к которому прицеплено это объявление
                foreach ($placements as $placement) {
                    if ($placement['item'] === 'ad_' . $ad->id) {           // $ad->id === $post->ID
                        $select[$placement['type']] = "title='местоположение этого объявления: $placement[name]'";
                        break;
                    }
                }
                $view = 'ad-verticalpositioning-metabox.php';
                // $hndlelinks = '<a href="' . esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ad-verticalpositioning-box' ) ) . '" target="_blank" class="vk_adnetwork-manual-link">' . esc_html__( 'Manual', 'vk-adnetwork' ) . '</a>';
                break;
            case 'ad-usage-box':                    // плашка Заметки (не обязательно) // Заметки § Шорткод § Шаблон
                $placements      = VK_Adnetwork::get_ad_placements_array(); // -TODO use model
                $show_codes = false;
                // если ЭТОТ рекламный блок (по вертикали!) === default, то покажем тут (в Заметках!) галочки Shortcode и PHP
/* шорткод пока заморожен! $show_codes всегда false
                foreach ($placements as $placement) {
                    if ($placement['item'] === 'ad_' . $ad->id && $placement['type'] === 'default') {           // $ad->id === $post->ID
                            $show_codes = true;
                            break;
                    }
                }
*/
                $view = 'ad-usage-metabox.php';
                $hndlelinks = '<a href="' . esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ad-usage-box' ) ) . '" target="_blank" class="vk_adnetwork-manual-link">' . esc_html__( 'Manual', 'vk-adnetwork' ) . '</a>';
                break;
            case 'ad-parameters-box':               // плашка Параметры объявлений  // Размер ширина 300 px высота 600 px § зарезервировать это пространство
                $view = 'ad-parameters-metabox.php';
                break;

            case 'ad-output-box':                   // плашка Режим отладки // Включить режим отладки (wordpress) Руководство § Включить режим отладки (VK AdNetwork)
                $wrapper_id         = $ad->options( 'output.wrapper-id', '' );
                $wrapper_class      = $ad->options( 'output.wrapper-class', '' );
                $debug_mode_enabled = (bool) $ad->options( 'output.debugmode', false );
                $mtdebug_mode_enabled = (bool) $ad->options( 'output.mtdebugmode', false );
                $view               = 'ad-output-metabox.php';
                $hndlelinks         = '<a href="' . esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ad-output-box' ) ) . '" target="_blank" class="vk_adnetwork-manual-link">' . esc_html__( 'Manual', 'vk-adnetwork' ) . '</a>';
                break;
        }

        if ( ! isset( $view ) ) {
            return;
        }
        // markup moved to handle headline of the metabox.
        if ( isset( $hndlelinks ) ) {
            ?><span class="vk_adnetwork-hndlelinks hidden">
            <?php
            echo wp_kses(
                $hndlelinks,
                [
                    'a' => [
                        'target' => [],
                        'href'   => [],
                        'class'  => [],
                    ],
                ]
            );
            ?>
                                                        </span>
            <?php
        }

        /**
         *  List general notices
         *  elements in $warnings contain [text] and [class] attributes.
         */
        $warnings = [];
        // show warning if ad contains https in parameters box.
        $https_message = VK_Adnetwork_Ad_Debug::is_https_and_http( $ad );
        if ( 'ad-parameters-box' === $box['id'] && $https_message ) {
            $warnings[] = [
                'text'  => $https_message,
                'class' => 'vk_adnetwork-ad-notice-https-missing vk_adnetwork-notice-inline vk_adnetwork-error',
            ];
        }



        $warnings = apply_filters( 'vk-adnetwork-ad-notices', $warnings, $box, $post );
        echo wp_kses('<ul id="' . esc_attr( $box['id'] ) . '-notices" class="vk_adnetwork-metabox-notices">', ['ul' => ['id' => true, 'class' => true]]);
        foreach ( $warnings as $_warning ) {
            if ( isset( $_warning['text'] ) ) :
                $warning_class = $_warning['class'] ?? '';
                echo wp_kses('<li class="' . esc_attr( $warning_class ) . '">' . $_warning['text'] . '</li>', ['li' => ['class' => true]]);
            endif;
        }
        echo wp_kses('</ul>', ['ul' => true]);
        include VK_ADNETWORK_BASE_PATH . 'admin/views/' . $view;
    }

    /**
     * Force all AA related meta boxes to stay visible
     *
     * @param array     $hidden       An array of hidden meta boxes.
     * @param WP_Screen $screen       WP_Screen object of the current screen.
     *
     * @return array
     */
    public function unhide_meta_boxes( $hidden, $screen ) {
        // only check on VK AdNetwork edit screen.
        if ( ! isset( $screen->id )
            || 'vk_adnetwork' !== $screen->id       // ad edit page. /wp-admin/post.php?post=480&action=edit
            || ! is_array( $this->meta_box_ids )
        ) {
            return $hidden;
        }

        // return only hidden elements which are not among the VK AdNetwork meta box ids.
        return array_diff( $hidden, (array) apply_filters( 'vk-adnetwork-unhide-meta-boxes', $this->meta_box_ids ) );
    }

    /**
     * Add dynamic CSS for un-hideable meta boxes.
     */
    public function unhide_meta_boxes_style() {
        $screen = get_current_screen();
        if ( empty( $screen ) || ! isset( $screen->id ) || 'vk_adnetwork' !== $screen->id ) {  // ad edit page. /wp-admin/post.php?post=480&action=edit
            return;
        }

        $meta_boxes = (array) apply_filters( 'vk-adnetwork-unhide-meta-boxes', $this->meta_box_ids );
        if ( empty( $meta_boxes ) ) {
            return;
        }

        $labels_list = implode( ', ',
            array_reduce( $meta_boxes,
                function( $styles, $box_id ) {
                    $styles[] = sprintf('label[for="%s-hide"]', esc_attr($box_id));
                    return $styles;
                }, []
            )
        );
        echo wp_kses(sprintf('<style>%s {display: none;}</style>', $labels_list), ['style' => true]);
    }

    /**
     * Add a meta box to post type edit screens with ad settings
     *
     * @param string $post_type current post type.
     */
    public function add_post_meta_box( $post_type = '' ) {
        // don’t display for non admins.
        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) ) ) {
            return;
        }

        // get public post types.
        $public_post_types = get_post_types(
            [
                'public'             => true,
                'publicly_queryable' => true,
            ],
            'names',
            'or'
        );

        // limit meta box to public post types.
        if ( in_array( $post_type, $public_post_types ) ) {
            add_meta_box(
                'vk_adnetwork-ad-settings',
                esc_html__( 'Ad Settings', 'vk-adnetwork' ),
                [ $this, 'render_post_meta_box' ],
                $post_type,
                'side',
                'low'
            );
        }
    }

    /**
     * Render meta box for ad settings on a per post basis
     *
     * @param WP_Post $post The post object.
     */
    public function render_post_meta_box( $post ) {

        // nonce field to check when we save the values.
        wp_nonce_field( 'vk_adnetwork_post_meta_box', 'vk_adnetwork_post_meta_box_nonce' );

        // retrieve an existing value from the database.
        $values = get_post_meta( $post->ID, '_vk_adnetwork_ad_settings', true );

        // load the view.
        include VK_ADNETWORK_BASE_PATH . 'admin/views/post-ad-settings-metabox.php';

        do_action( 'vk_adnetwork_render_post_meta_box', $post, $values );
    }

    /**
     * Save the ad meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     *
     * @return mixed empty or post ID.
     */
    public function save_post_meta_box( $post_id ) {

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) ) )
            return;

        // check nonce.
        if ( ! isset( $_POST['vk_adnetwork_post_meta_box_nonce'] ) )
            return $post_id;

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['vk_adnetwork_post_meta_box_nonce'])), 'vk_adnetwork_post_meta_box' ) )
            return $post_id;

        // don’t save on autosave.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // check the user's permissions.
        if ( 'page' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        // sanitize the user input.
        $_data['disable_ads'] = isset( $_POST['vk_adnetwork']['disable_ads'] ) ? absint( $_POST['vk_adnetwork']['disable_ads'] ) : 0;

        $_data = apply_filters( 'vk_adnetwork_save_post_meta_box', $_data );

        // update the meta field.
        update_post_meta( $post_id, '_vk_adnetwork_ad_settings', $_data );
    }

    /**
     * Add "close" class to collapse the ad-type metabox after ad was saved first
     *
     * @param array $classes class attributes.
     * @return array $classes
     */
    public function close_ad_type_metabox( $classes = [] ) {
        global $post;
        if ( isset( $post->ID ) && 'publish' === $post->post_status ) {
            if ( ! in_array( 'closed', $classes, true ) ) {
                $classes[] = 'closed';
            }
        } else {
            $classes = [];
        }
        return $classes;
    }

    /**
     * Add dashboard widget with ad stats and additional information
     */
    public function add_dashboard_widget() {
        // display dashboard widget only to authors and higher roles.
        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_see_interface' ) ) ) {
                return;
        }
        add_meta_box( 'vk_adnetwork_dashboard_widget', esc_html__( 'Dashboard', 'vk-adnetwork' ), [ $this, 'dashboard_widget_function' ], 'dashboard', 'side', 'high' );
    }

    /**
     * Display widget functions
     *
     * @param WP_Post $post post object.
     * @param array   $callback_args callback arguments.
     */
    public static function dashboard_widget_function( $post, $callback_args ) {
        // get number of ads.
        $ads_count = VK_Adnetwork::get_number_of_ads();
        if ( current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) ) ) {
            echo wp_kses('<p>' .
                sprintf(
                    // translators: %1$d is the number of ads, %2$s and %3$s are URLs.
                    __( '%1$d ads – <a href="%2$s">manage</a> - <a href="%3$s">new</a>', 'vk-adnetwork' ),
                    absint( $ads_count ),
                    'edit.php?post_type=' . esc_attr( VK_Adnetwork::POST_TYPE_SLUG ),
                    'post-new.php?post_type=' . esc_attr( VK_Adnetwork::POST_TYPE_SLUG )
                )
                . '</p>',
                [ 'a' => [ 'href' => true ], 'p' => true ]
            );
        }

        ?>
        <p><a href="<?php echo esc_url( VK_ADNETWORK_URL . 'help/categories/partner' ); ?>" target="_blank">
                <?php // Посетите наш блог и прочтите больше статей об оптимизации рекламы
                esc_html_e( 'Visit our blog for more articles about ad optimization', 'vk-adnetwork' ); ?></a>
        </p>
        <?php
    }

    /**
     * Fixes a WP QUADS PRO compatibility issue
     * they inject their ad optimization meta box into our ad page, even though it is not a public post type
     * using they filter, we remove VKAN from the list of post types they inject this box into
     *
     * @param array $allowed_post_types array of allowed post types.
     * @return array
     */
    public function fix_wpquadspro_issue( $allowed_post_types ) {
        unset( $allowed_post_types['vk_adnetwork'] );
        return $allowed_post_types;
    }

}
