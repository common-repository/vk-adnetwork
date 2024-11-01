<?php
/**
 * VK AdNetwork main admin class
 *
 * @package   VK_Adnetwork_Admin
 * @license   GPL-2.0+
 * @link      https://vk.com
 * @copyright since 2023 VK
 *
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 */
class VK_Adnetwork_Admin {

    /**
     * Instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Instance of admin notice class.
     *
     * @var      object $notices
     */
    protected $notices = null;

    /**
     * Slug of the settings page
     *
     * @var      string $plugin_screen_hook_suffix
     */
    public $plugin_screen_hook_suffix = VK_ADNETWORK_SLUG . '-settings'; // null;

    /**
     * General plugin slug
     *
     * @var     string
     */
    protected $plugin_slug = '';

    /**
     * Admin settings.
     *
     * @var      array
     */
    protected static $admin_settings = null;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     */
    private function __construct() {
        if ( wp_doing_ajax() ) {
            new VK_Adnetwork_Ad_Ajax_Callbacks();
            add_action( 'plugins_loaded', [ $this, 'wp_plugins_loaded_ajax' ] );
        } else {
            add_action( 'plugins_loaded', [ $this, 'wp_plugins_loaded' ] );
            VK_Adnetwork_Ad_List_Filters::get_instance();
        }
    }


    /**
     * Actions and filter available after all plugins are initialized.
     */
    public function wp_plugins_loaded() {
        // call $plugin_slug from public plugin class.
        $plugin            = VK_Adnetwork::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();

        add_action( 'current_screen', [ $this, 'current_screen' ] );

        // Load admin style sheet and JavaScript.
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 9 );

        // add VK AdNetwork admin notices
        // removes admin notices from other plugins
        // `in_admin_header` is the last hook to run before àdmin_notices` according to https://codex.wordpress.org/Plugin_API/Action_Reference.
        add_action( 'in_admin_header', [ $this, 'register_admin_notices' ] );

        // add links to plugin page.
        add_filter( 'plugin_action_links_' . VK_ADNETWORK_BASE, [ $this, 'add_plugin_links' ] );

        // display information when user is going to disable the plugin.
        add_filter( 'admin_footer', [ $this, 'add_deactivation_logic' ] );
        // add_filter( 'after_plugin_row_' . VK_ADNETWORK_BASE, array( $this, 'display_deactivation_message' ) );


        add_action( 'admin_action_update', [ $this, 'redirect_after_admin_action_update' ]);

        VK_Adnetwork_Admin_Meta_Boxes::get_instance();
        VK_Adnetwork_Admin_Menu::get_instance();
        VK_Adnetwork_Admin_Ad_Type::get_instance();
        VK_Adnetwork_Admin_Settings::get_instance();
    }

    /**
     * это перед сохранением Настроек -- (акция admin_action_update)
     * wp_redirect х 2:
     * 1) если не было токенов -- создадим площадку и пад + редирект на страницу Реклама (+нотис)
     * 2) если галочка удалить токены -- удалим всё + редирект на эту же страницу Опции + &message=... (=нотис)
     * 3) ни то, ни другое -- всё как обычно (ничего тут не делаем)
     */
    public function redirect_after_admin_action_update() {
        if (!check_admin_referer('vk_adnetwork-options', 'vk_adnetwork_options_nonce')) return;
        if (!isset($_POST['vk-adnetwork']))                                             return;

        $options = []; // instead VK_Adnetwork_Utils::vk_adnetwork_sanitize_array(); // тут новые значения из формы -- еще не сохранённые
        $vk_adnetwork_creds = [];

        if ($_POST['vk-adnetwork'] && is_array($_POST['vk-adnetwork'])) {
            foreach (['group_id', 'pad_id', 'slot_id', 'post_id'] as $i) {
                if (isset($_POST['vk-adnetwork'][$i]))
                    $options[$i] = absint($_POST['vk-adnetwork'][$i]);
            }
        }
        if ($_POST['vk-adnetwork']['vk-adnetwork-creds'] && is_array($_POST['vk-adnetwork']['vk-adnetwork-creds'])) { // client_id/secret|access/refresh_token == [0-9A-Za-z]+ (sanitize ~ 1:1)
            foreach (['client_id', 'client_secret', 'access_token', 'refresh_token', 'delete_tokens', 'tokens_left'] as $cred) {
                if (isset($_POST['vk-adnetwork']['vk-adnetwork-creds'][$cred]))
                    $vk_adnetwork_creds[$cred] = sanitize_text_field($_POST['vk-adnetwork']['vk-adnetwork-creds'][$cred]);
            }
            $options['vk-adnetwork-creds'] = $vk_adnetwork_creds;
        }
        if (isset($_POST['vk-adnetwork']['vk-adnetwork-hide-for-user-role']) && is_array($_POST['vk-adnetwork']['vk-adnetwork-hide-for-user-role'])) { // -x- array_intersect()
            foreach (['administrator', 'editor', 'author'] as $role) {
                if (in_array($role, $_POST['vk-adnetwork']['vk-adnetwork-hide-for-user-role']))
                    $options['vk-adnetwork-hide-for-user-role'][] = $role;
            }
        }
        if (isset($_POST['vk-adnetwork']['vk-adnetwork-disabled-ads']) && is_array($_POST['vk-adnetwork']['vk-adnetwork-disabled-ads'])) { // -x- array_intersect()
            if (isset($_POST['vk-adnetwork']['vk-adnetwork-disabled-ads']['all']))
                $options['vk-adnetwork-disabled-ads']['all'] = 1;
        }
        if (isset($_POST['vk-adnetwork']['vk-adnetwork-uninstall-delete-data']))
            $options['vk-adnetwork-uninstall-delete-data'] = 1;

        if ($vk_adnetwork_creds['delete_tokens'] ?? 0) {
            $tokens = VK_Adnetwork_Utils::vk_adnetwork_oauth2_token('', $options); // удаление всех токенов (и в MT, и в WP)
            // НЕ продолжаем сохранение опций -- чтобы опции из $_POST не перезатёрли удаление
            wp_redirect(admin_url("admin.php?page=vk-adnetwork-settings&message=$tokens[message]"));
            exit;
        }elseif ($vk_adnetwork_creds['client_id'] && $vk_adnetwork_creds['client_secret'] && !$vk_adnetwork_creds['access_token'] && !$vk_adnetwork_creds['refresh_token']) { // когда ЕСТЬ клиент-ИД и клиент-секрет, а токенов нет
            $tokens = VK_Adnetwork_Utils::vk_adnetwork_oauth2_token('client_credentials', $options);          //  =>  запрашиваем у МТ grant_type=client_credentials
            // заодно, если всё ОК --  и $_POST['vk-adnetwork'] сохранили в VK_Adnetwork::get_instance()->update_options()
            if (isset($tokens['access_token']) && !isset($tokens['error'])) {
                $options['vk-adnetwork-creds']['access_token']  = $tokens['access_token'];
                $options['vk-adnetwork-creds']['refresh_token'] = $tokens['refresh_token'];
                $options['vk-adnetwork-creds']['tokens_left']   = $tokens['tokens_left'];

                $data = VK_Adnetwork_Utils::vk_adnetwork_group_pads_post('');                                       //   =>  =>  создадим площадку и пад
                if (isset($data['error'])) {
                    echo wp_kses('Не удалось создать площадку -- ошибка:<pre>' . print_r($data['error'], true) . '</pre>', ['pre' => true]);
                } elseif (isset($data['id'])) {
                    $setupdata = VK_Adnetwork_Utils::vk_adnetwork_setup_xml($data['id']);                             // достанем vk_adnetwork_setup.xml, заменим в нём макросы и создадим из него объявление
                    if ($setupdata['slot_id']) {
                        // код вставки рекламы добавлен
                        $options['group_id'] = $setupdata['group_id'];                                               // площадка, в теории, одна м.б. -- сохраним её ИД
                        $options['pad_id']   = $setupdata['pad_id'];                                                 // а ПАД и СЛОТ -- сохраним как последние созданные
                        $options['slot_id']  = $setupdata['slot_id'];                                                // (их м.б. много)
                        $options['post_id']  = $setupdata['post_id'];                                                // (их м.б. много)
                        VK_Adnetwork::get_instance()->update_options($options);                                      //  -x- update_option(VK_ADNETWORK_SLUG, $options);
                        // два редиректа ниже: К.К. настойчиво просит перекидывать на именно -- страницу-список-из-одной-рекламы
                        // (а не на страницу-редактирования-этой-новой-рекламы) (чтобы вернуть -- просто первый wp_redirect комментим, второй раскомментим)
                        // + нотис! = setupdata[message] = vk-adnetwork-starter-setup-success
                        wp_redirect(admin_url("edit.php?post_type=vk_adnetwork&message=$setupdata[message]"));                     // Перенаправление на страницу "Реклама"
                        // wp_redirect(admin_url("post.php?post=$setupdata[post_id]&action=edit&message=$setupdata[message]"));    // Перенаправление на страницу Реклама post_id
                        exit;
                        // VK_Adnetwork_Admin::get_instance()->starter_setup_success_message();
                        /* $redirecthref = admin_url("edit.php?post_type=vk_adnetwork&message=vk-adnetwork-starter-setup-success&post=$setupdata[post_id]");
                        echo "<script> jQuery( document ).ready(function () { window.location.href = '$redirecthref' }); </script>";
                        return; */
                    }
                }
            }else if (isset($tokens['error'])) {
                $err = "$tokens[error]: $tokens[error_description]";
                error_log("class-vk-adnetwork-admin.redirect_after_admin_action_update: ERROR! $err");
                wp_redirect(admin_url("admin.php?page=vk-adnetwork-settings&message=$err"));
                exit;
            }else{
                $err = print_r($tokens);
                error_log("class-vk-adnetwork-admin.redirect_after_admin_action_update: EMPTY ERROR! $err");
                wp_redirect(admin_url("admin.php?page=vk-adnetwork-settings&message=$err"));
                exit;
            }
        }
    }

    /**
     * Actions and filters that should also be available for ajax
     */
    public function wp_plugins_loaded_ajax() {
        // needed here in order to work with Quick Edit option on ad list page.
        VK_Adnetwork_Admin_Ad_Type::get_instance();

        add_action( 'wp_ajax_vk_adnetwork_send_feedback', [ $this, 'send_feedback' ] );
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
     * General stuff after page is loaded and screen variable is available
     */
    public function current_screen() {
        $screen = get_current_screen();

        if ( ! isset( $screen->id ) ) {
            return;
        }

        switch ( $screen->id ) {
            case 'edit-vk_adnetwork':   // ad overview page. /wp-admin/edit.php?post_type=vk_adnetwork
            case 'vk_adnetwork':        // ad edit page.     /wp-admin/post.php?post=480&action=edit
                // remove notice about missing first ad.
                break;
        }
    }

    /**
     * Register and enqueue admin-specific style sheet.
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style( $this->plugin_slug . '-ui-styles', plugins_url( 'assets/css/ui.css', __FILE__ ), [], VK_ADNETWORK_VERSION );
        wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), [], VK_ADNETWORK_VERSION );

        $screen = get_current_screen();
        if ( ! $screen instanceof WP_Screen) {
            return;
        }

//        if ( $screen->post_type === VK_Adnetwork::POST_TYPE_SLUG && $screen->base === 'post' ) {
//            wp_enqueue_style( $this->plugin_slug . '-ad-positioning-styles', VK_ADNETWORK_BASE_URL . '/modules/ad-positioning/assets/css/ad-positioning.css', [ $this->plugin_slug . '-admin-styles' ], VK_ADNETWORK_VERSION );
//        }
    }

    /**
     * Register and enqueue admin-specific JavaScript.
     */
    public function enqueue_admin_scripts() {

        // global js script.
        wp_enqueue_script( $this->plugin_slug . '-admin-global-script', plugins_url( 'assets/js/admin-global.js', __FILE__ ), [ 'jquery' ], VK_ADNETWORK_VERSION, false );
        wp_enqueue_script( $this->plugin_slug . '-admin-find-adblocker', plugins_url( 'assets/js/advertisement.js', __FILE__ ), [], VK_ADNETWORK_VERSION, false );

        // register ajax nonce.
        $params = [
            'ajax_nonce' => wp_create_nonce( 'vk-adnetwork-admin-ajax-nonce' ),
        ];
        wp_localize_script( $this->plugin_slug . '-admin-global-script', 'vk_adnetwork_global', $params );

        if ( self::screen_belongs_to_vk_adnetwork() ) {
            wp_register_script( $this->plugin_slug . '-ui-scripts', plugins_url( 'assets/js/ui.js', __FILE__ ), [ 'jquery' ], VK_ADNETWORK_VERSION, false );

            $this->enqueue_main_admin_script();

            // register admin.js translations.
            $translation_array = [
                'condition_or'                  => esc_html__( 'or', 'vk-adnetwork' ),
                'condition_and'                 => esc_html__( 'and', 'vk-adnetwork' ),
                'after_paragraph_promt'         => esc_html__( 'After which paragraph?', 'vk-adnetwork' ),
                'today'                         => esc_html__( 'Today', 'vk-adnetwork' ),
                'yesterday'                     => esc_html__( 'Yesterday', 'vk-adnetwork' ),
                'this_month'                    => esc_html__( 'This Month', 'vk-adnetwork' ),
                /* translators: 1: The number of days. */
                'last_n_days'                   => esc_html__( 'Last %1$d days', 'vk-adnetwork' ),
                /* translators: 1: An error message. */
                'error_message'                 => esc_html__( 'An error occurred: %1$s', 'vk-adnetwork' ), // Произошла ошибка: %1$s
                'all'                           => esc_html__( 'All', 'vk-adnetwork' ),
                'no_results'                    => esc_html__( 'There were no results returned for this ad. Please make sure it is active, generating impressions and double check your ad parameters.', 'vk-adnetwork' ),
                'show_inactive_ads'             => esc_html__( 'Show inactive ads', 'vk-adnetwork' ),
                'hide_inactive_ads'             => esc_html__( 'Hide inactive ads', 'vk-adnetwork' ),
                'delete_placement_confirmation' => esc_html__( 'Permanently delete this placement?', 'vk-adnetwork' ),
                'close'                         => esc_html__( 'Close', 'vk-adnetwork' ),
                'confirmation'                  => esc_html__( 'Data you have entered has not been saved. Are you sure you want to discard your changes?', 'vk-adnetwork' ),
                'admin_page'                    => self::get_vk_adnetwork_admin_screen(),
                'placements_allowed_ads'        => [
                    'action' => 'vk_adnetwork-placements-allowed-ads',
                    'nonce'  => wp_create_nonce( 'vk_adnetwork-placements-allowed-ads' ),
                ],
            ];

            wp_localize_script( $this->plugin_slug . '-admin-script', 'vk_adnetwork_txt', $translation_array );

            wp_enqueue_script( $this->plugin_slug . '-admin-script' );
        }

        // call media manager for image upload only on ad edit pages.
        $screen = get_current_screen();

        if ( ! $screen instanceof WP_Screen) {
            return;
        }

        if ( isset( $screen->id ) && VK_Adnetwork::POST_TYPE_SLUG === $screen->id ) {
            // the 'wp_enqueue_media' function can be executed only once and should be called with the 'post' parameter
            // in this case, the '_wpMediaViewsL10n' js object inside html will contain id of the post, that is necessary to view oEmbed priview inside tinyMCE editor.
            // since other plugins can call the 'wp_enqueue_media' function without the 'post' parameter, VK AdNetwork should call it earlier.
            global $post;
            wp_enqueue_media( [ 'post' => $post ] );
        }

        // single ad edit screen.
//        if ( $screen->post_type === VK_Adnetwork::POST_TYPE_SLUG && $screen->base === 'post' ) {
//            wp_enqueue_script(
//                $this->plugin_slug . '-ad-positioning-script',
//                VK_ADNETWORK_BASE_URL . '/modules/ad-positioning/assets/js/ad-positioning.js',
//                [],
//                VK_ADNETWORK_VERSION,
//                true
//            );
//        }

        // admin.php?page=vk-adnetwork = Статистика
        if ( $screen->base === 'toplevel_page_vk-adnetwork' ) {
            wp_enqueue_script(
                $this->plugin_slug . '-ad-chart-script',
                plugins_url( 'assets/js/npm-chart.js', __FILE__ ),
                [],
                VK_ADNETWORK_VERSION,
                ['in_footer'  => true]
            );
        }
    }

    /**
     * Enqueue the minified version of the admin script, or the parts if SCRIPT_DEBUG is defined and true.
     *
     * @return void
     */
    private function enqueue_main_admin_script() {
        $dependencies = [ 'jquery', $this->plugin_slug . '-ui-scripts', 'jquery-ui-autocomplete', 'wp-util' ];

        if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
            wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), $dependencies, VK_ADNETWORK_VERSION, false );
            $dependencies[] = $this->plugin_slug . '-admin-script';

            wp_enqueue_script( $this->plugin_slug . '-admin-script-termination', plugins_url( 'assets/js/termination.js', __FILE__ ), $dependencies, VK_ADNETWORK_VERSION, false );
            $dependencies[] = $this->plugin_slug . '-admin-script-termination';

            wp_enqueue_script( $this->plugin_slug . '-admin-script-dialog', plugins_url( 'assets/js/dialog-vk_adnetwork-modal.js', __FILE__ ), $dependencies, VK_ADNETWORK_VERSION, false );

            return;
        }

        wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.min.js', __FILE__ ), $dependencies, VK_ADNETWORK_VERSION, false );
    }

    /**
     * Check if the current screen belongs to VK AdNetwork
     *
     * @return bool
     */
    public static function screen_belongs_to_vk_adnetwork() {
        return self::get_vk_adnetwork_admin_screen() !== '';
    }

    /**
     * Get the current screen id if the page belongs to AA, otherwise empty string.
     *
     * @return string
     */
    private static function get_vk_adnetwork_admin_screen() {
        if ( ! function_exists( 'get_current_screen' ) ) {
            return '';
        }

        $screen = get_current_screen();
        if ( ! isset( $screen->id ) ) {
            return '';
        }

        $vk_adnetwork_pages = apply_filters(
            'vk-adnetwork-dashboard-screens',
            [
                'edit-vk_adnetwork',                         // ads overview  /wp-admin/edit.php?post_type=vk_adnetwork
                'vk_adnetwork',                              // ad edit page  /wp-admin/post.php?post=480&action=edit

                // не понимаю, почему у "Настройки" иногда такой ИД, а иногда другой?
                // (м.б. т.к. когда (в начале) "Настройки" ЕДИНСТВЕННАЯ страница? и она тогда топлевел?)
                'vk-adnetwork_page_vk-adnetwork-settings',   // settings      /wp-admin/admin.php?page=vk-adnetwork-settings
                    'toplevel_page_vk-adnetwork-settings',   // settings      /wp-admin/admin.php?page=vk-adnetwork-settings (?)

                'toplevel_page_vk-adnetwork',                // overview      /wp-admin/admin.php?page=vk-adnetwork
                'admin_page_vk-adnetwork-import-export',     // import&export /wp-admin/admin.php?page=vk-adnetwork-import-export
                'vk-adnetwork_page_vk-adnetwork-support',    // support       /wp-admin/admin.php?page=vk-adnetwork-support
                // 'admin_page_vk-adnetwork-debug',          // debug XZ!
            ]
        );

        if ( ! in_array( $screen->id, $vk_adnetwork_pages, true ) ) {
            return '';
        }

        return $screen->id;
    }

    /**
     * Get action from the params
     */
    public function current_action() {

        if ( isset( $_REQUEST['action'] ) && -1 !== $_REQUEST['action'] ) {     // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        }
        return false;
    }

    /**
     * Get literal expression of timezone.
     *
     * @param DateTimeZone $date_time_zone the DateTimeZone object to get literal value from.
     *
     * @return string time zone.
     * @see        VK_Adnetwork_Utils::vk_adnetwork_get_timezone_name()
     *
     * @deprecated This is also used outside of admin as well as other plugins.
     */
    public static function timezone_get_name( DateTimeZone $date_time_zone ) {
        return VK_Adnetwork_Utils::vk_adnetwork_get_timezone_name();
    }

    /**
     * Registers VK AdNetwork admin notices
     * prevents other notices from showing up on our own pages
     */
    public function register_admin_notices() {

        /**
         * Remove all registered admin_notices from AA screens
         * we need to use this or some users have half or more of their viewports cluttered with unrelated notices
         */
        if ( $this->screen_belongs_to_vk_adnetwork() ) {
            remove_all_actions( 'admin_notices' );
        }

        // register our own notices.
        add_action( 'admin_notices', [ $this, 'admin_notices' ] );
    }

    /**
     * Initiate the admin notices class
     */
    public function admin_notices() {
        // display ad block warning to everyone who can edit ads.
        if ( current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) ) ) {
            if ( $this->screen_belongs_to_vk_adnetwork() ) {
                include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/adblock.php';
            }
        }

        if ( $this->screen_belongs_to_vk_adnetwork() ) {
            $this->branded_admin_header();
        }

        // Show success notice after starter setup was imported. Registered here because it will be visible only once.
        // &message=vk-adnetwork-starter-setup-success
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_REQUEST['message'] ) && 'vk-adnetwork-starter-setup-success' === $_REQUEST['message'] ) {
            add_action( 'vk-adnetwork-admin-notices', [ $this, 'starter_setup_success_message' ] );
        }

        // register our own notices on VK AdNetwork pages, except from the overview page where they should appear in the notices section.
        $screen = get_current_screen();
        if ( class_exists( 'VK_Adnetwork_Admin_Notices' )
             && current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) )
             && ( ! isset( $screen->id ) || 'toplevel_page_vk-adnetwork' !== $screen->id )      // overview.     /wp-admin/admin.php?page=vk-adnetwork
        ) {
            $this->notices = VK_Adnetwork_Admin_Notices::get_instance()->notices;

            echo wp_kses('<div class="wrap">', ['div' => ['class' => true]]);
            VK_Adnetwork_Admin_Notices::get_instance()->display_notices();

            // allow other VK AdNetwork plugins to show admin notices at this late stage.
            do_action( 'vk-adnetwork-admin-notices' );
            echo wp_kses('</div>', ['div' => true]);
        }
    }

    /**
     * Add links to the plugins list
     *
     * @param array $links array of links for the plugins, adapted when the current plugin is found.
     *
     * @return array $links
     */
    public function add_plugin_links( $links ) {
        if ( ! is_array( $links ) ) {
            return $links;
        }

        // add link to support page.
        $support_link = '<a href="' . esc_url( admin_url( 'admin.php?page=vk-adnetwork-support' ) ) . '">' . esc_html__( 'Support', 'vk-adnetwork' ) . '</a>';
        array_unshift( $links, $support_link );

        return $links;
    }

    /**
     * Display deactivation logic on plugins page
     *
     * @since 1.7.14
     */
    public function add_deactivation_logic() {
        $screen = get_current_screen();
        if ( ! isset( $screen->id ) || ! in_array( $screen->id, [ 'plugins', 'plugins-network' ], true ) ) {
            return;
        }

        $current_user = wp_get_current_user();
        if ( ! ( $current_user instanceof WP_User ) ) {
            $from  = '';
            $email = '';
        } else {
            $from  = $current_user->user_nicename . ';' . trim( $current_user->user_email );
            $email = $current_user->user_email;
        }

        include VK_ADNETWORK_BASE_PATH . 'admin/views/feedback-disable.php';
    }

    /**
     * Send feedback via email
     *
     * @since 1.7.14
     */
    public function send_feedback() {

        /** @noinspection PhpUndefinedVariableInspection */
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vk_adnetwork_disable_form_nonce'] ?? '')), 'vk_adnetwork_disable_form')) {
            die();
        }

        // below to replace -- $text = sanitize_textarea_field(implode("\n", _POST['vk_adnetwork_disable_text'] ?? []));
        $text = '';
        if (is_array($_POST['vk_adnetwork_disable_text']))
            foreach ($_POST['vk_adnetwork_disable_text'] as $t1)
                $text .= sanitize_textarea_field($t1) . "\n";

        // get first version to see if this is a new problem or might be an older on.
        $options   = VK_Adnetwork_Plugin::get_instance()->internal_options();
        $installed = isset( $options['installed'] ) ? gmdate( 'd.m.Y', $options['installed'] ) : '–';

        $text .= "\n\n" . home_url() . " ($installed)";

        $headers = [];


        if (strpos($_POST['vk_adnetwork_disable_from'], ';')) {
            [$name, $email] = explode(';', sanitize_text_field($_POST['vk_adnetwork_disable_from']));
            $email = sanitize_email($email);
            $from = "$name <$email>";
        }else{
            $from = sanitize_email($_POST['vk_adnetwork_disable_from']);
        }

        // the user clicked on the "don’t disable" button or if an address is given in the form then use that one.
        if ( isset( $_POST['vk_adnetwork_disable_reason'] )
             && 'get help' === $_POST['vk_adnetwork_disable_reason']
             && ! empty( $_POST['vk_adnetwork_disable_reply_email'] ) ) {
            $email = sanitize_email( $_POST['vk_adnetwork_disable_reply_email'] );
            if ( ! is_email( $email ) ) {
                die();
            }

            $current_user = wp_get_current_user();
            $name         = ( $current_user instanceof WP_User ) ? $current_user->user_nicename : '';
            $from         = $name . ' <' . $email . '>';
            if ( isset( $_POST['vk_adnetwork_disable_text'][0] )
                 && trim( $_POST['vk_adnetwork_disable_text'][0] ) !== '' ) { // is a text given then ask for help.
                $text .= "\n\n Help is on its way.";
            } else { // if no text is given, just reply.
                $text .= "\n\n Thank you for your feedback.";
            }
        }
        if ( $from ) {
            $headers[] = "From: $from";
            $headers[] = "Reply-To: $from";
        }

        $subject = sanitize_text_field($_POST['vk_adnetwork_disable_reason']) ?? '(no reason given)';
        // append plugin name to get a better subject.
        $subject .= ' (VK AdNetwork)';

        $success = wp_mail( 'adnetwork_support@vk.company', $subject, $text, $headers );

        die();
    }

    /**
     * Show success message after starter setup was created.
     */
    public function starter_setup_success_message() {

        // load link to latest post., 'post_type' => 'vk_adnetwork'

        $args           = [ 'numberposts' => 1 ]; // это просто последний пост на сайте для 'See them in action'
        $last_post      = get_posts( $args );
        $last_post_link = isset( $last_post[0]->ID ) ? get_permalink( $last_post[0]->ID ) : false;
        $options        = VK_Adnetwork::get_instance()->options();
        $wphost         = VK_Adnetwork_Utils::vk_adnetwork_wphost();

        if (isset($options['group_id']) && isset($options['pad_id']) && isset($options['post_id']) && isset($options['slot_id']))
            include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/starter-setup-success.php';
    }

    /**
     * Add an VK AdNetwork branded header to plugin pages
     *
     * @see VK_Adnetwork_Admin::screen_belongs_to_vk_adnetwork()
     */
    private function branded_admin_header() {
        $screen              = get_current_screen();
        $manual_url          = 'manual/';
        $new_button_id       = '';
        $new_button_label    = '';
        $new_button_href     = '';
        $back_button_id       = '';
        $back_button_label    = '';
        $back_button_href     = '';
        $show_filter_button  = false;
        $reset_href          = '';
        $filter_disabled     = '';
        $show_screen_options = false;
        $title               = get_admin_page_title();
        $tooltip             = '';

        $options = VK_Adnetwork::get_instance()->options();
        $newuser = empty($options['vk-adnetwork-creds']['client_id']) || empty($options['vk-adnetwork-creds']['client_secret']);

        switch ( $screen->id ) {

            case 'vk_adnetwork':            // ad edit page. /wp-admin/post.php?post=480&action=edit
                // Новый рекламный блок
                $new_button_label = esc_html__( 'New Ad', 'vk-adnetwork' );
                $new_button_href  = admin_url( 'post-new.php?post_type=vk_adnetwork' );
                $back_button_label    = esc_html__( 'Back to list', 'vk-adnetwork' );
                $back_button_href     = admin_url( 'edit.php?post_type=vk_adnetwork' );
                $manual_url       = 'manual/first-ad/';
                break;

            case 'edit-vk_adnetwork':            // ads overview. /wp-admin/edit.php?post_type=vk_adnetwork
                // Реклама
                $title               = esc_html__( 'Ads', 'vk-adnetwork' );
                // Новый рекламный блок
                $new_button_label    = esc_html__( 'New Ad', 'vk-adnetwork' );
                $new_button_href     = admin_url( 'post-new.php?post_type=vk_adnetwork' );
                $manual_url          = 'manual/first-ad/';
                $show_filter_button  = ! VK_Adnetwork_Ad_List_Filters::uses_filter_or_search();
                $reset_href          = ! $show_filter_button ? esc_url( admin_url( 'edit.php?post_type=' . VK_Adnetwork::POST_TYPE_SLUG ) ) : '';
                $filter_disabled     = $screen->get_option( 'show-filters' ) ? 'disabled' : '';
                $show_screen_options = true;
                break;

            case 'vk-adnetwork_page_vk-adnetwork-settings':            // settings.     /wp-admin/admin.php?page=vk-adnetwork-settings
                $title              = $newuser
                    // 'Регистрация в VK AdNetwork' кастомный заголовок страницы Настройки, если пустые ИД и Секрет
                    ? esc_html__( 'Registration in VK AdNetwork', 'vk-adnetwork' )
                    : esc_html__( 'Settings', 'vk-adnetwork' );
                $new_button_href  = '';
                break;
        }

        $manual_url = apply_filters( 'vk-adnetwork-admin-header-manual-url', $manual_url, $screen->id );

        include VK_ADNETWORK_BASE_PATH . 'admin/views/header.php';
    }
}
