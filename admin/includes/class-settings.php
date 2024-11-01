<?php

/**
 * Class VK_Adnetwork_Admin_Settings
 */
class VK_Adnetwork_Admin_Settings {
    /**
     * Instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * VK_Adnetwork_Admin_Settings constructor.
     */
    private function __construct() {
        // settings handling.
        add_action( 'admin_init', [ $this, 'settings_init' ] );

        // add ad admin capabilities for settings.
        add_action( 'admin_init', [ $this, 'settings_capabilities' ], 20 );
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
     * Initialize settings (Settings ~ Настройки | VK AdNetwork Settings ~ Настройки VK AdNetwork)
     *
     * @since 1.0.1
     */
    public function settings_init() {

        // get settings page hook.
        $hook = VK_Adnetwork_Admin::get_instance()->plugin_screen_hook_suffix;

        // register settings.
        register_setting( VK_ADNETWORK_SLUG, VK_ADNETWORK_SLUG, [ $this, 'sanitize_settings' ] );

        $options = VK_Adnetwork::get_instance()->options();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $newuser = empty($options['vk-adnetwork-creds']['client_id']) || empty($options['vk-adnetwork-creds']['client_secret']);

        // "vk-adnetwork-creds" settings section
        // Блок "Настройки VK AdNetwork API" из 4 п.п.
        // Для работы Вам необходимо <a href="%s">зарегистрироваться</a> в рекламной системе <b>VK AdNetwork</b> и получить ключ доступа к API (<a href="%s">как получить ключи API</a>
        // 'To work, you need to <a href="%s">register</a>in the advertising system <b>VK AdNetwork</b> and get an API access key (<a href="%s">how to get API keys</a>)'
        add_settings_section(
            'vk_adnetwork_setting_section_vk_adnetwork_creds',
                        '',
            [ $this, 'render_settings_section_vk_adnetwork_creds_callback' ],
            $hook
        );

        // после удаления токенов редиректим на admin.php?page=vk-adnetwork-settings&message=Токены удалены и сохранены
        // и хотим увидеть на странице инфу про это: (i) Токены удалены и сохранены ____
        if ($_GET['message'] ?? '')     // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            add_settings_field(
                'vk_adnetwork_message',
                                '',
                // '<img src="' . VK_ADNETWORK_BASE_URL . 'admin/assets/img/i.png" alt="(i)"> '
                // . $_GET['message']
                // . '<hr>',
                [ $this, 'render_vk_adnetwork_message' ],
                $hook,
                'vk_adnetwork_setting_section_vk_adnetwork_creds'
            );

        // Токены >> [ client_id , client_secret ,, access_token , refresh_token ,, tokens_left , delete_tokens ]
        add_settings_field(
            'vk-adnetwork-creds',
            // Ключи доступа к API VK AdNetwork
                        '',
            // esc_html__( 'VK AdNetwork API Access Keys', 'vk-adnetwork' ),
            [ $this, 'render_settings_vk_adnetwork_creds' ],
            $hook,
            'vk_adnetwork_setting_section_vk_adnetwork_creds'
        );

        if ($newuser) return; // если нет client_id / client_secret -- то больше ничего не показываем!

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // "Disable ads" settings section
        // Блок "Отключить рекламу" из 4 п.п.
        add_settings_section(
            'vk_adnetwork_setting_section_hide_ads',
            // Блок настроек отключения рекламы
                        'Скрытие рекламы',
            // esc_html__( 'Block of settings for disabling ads', 'vk-adnetwork' ),
            [ $this, 'render_settings_section_disable_ads_callback' ],
            $hook
        );

            // add setting fields for user role
            // Скрыть объявления для пользователей этих ролей
            add_settings_field(
                'vk-adnetwork-hide-for-user-role',
                // esc_html__( 'Hide ads for user roles', 'vk-adnetwork' ),
                                '',
                [ $this, 'vk_adnetwork_render_settings_hide_for_users' ],
                $hook,
                'vk_adnetwork_setting_section_hide_ads'
            );

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // "Disable ads" settings section
        // Блок "Отключить рекламу" из 4 п.п.
        add_settings_section(
                    'vk_adnetwork_setting_section_disable_ads',
                    // Блок настроек отключения рекламы
                    esc_html__( 'Disable all ads on the blog', 'vk-adnetwork' ),
                    [ $this, 'render_settings_section_disable_ads_callback' ],
                    $hook
            );

                    // add setting fields to disable ads
                    // Отключить рекламу (6 галочек: всю, 404, архив, вторичка, РСС, РЕСТ) -- оставили одну галочку
                    add_settings_field(
                            'vk-adnetwork-disable-ads',
                            // esc_html__( 'Disable ads', 'vk-adnetwork' ),
                            '',
                            [ $this, 'vk_adnetwork_render_settings_disable_ads' ],
                            $hook,
                            'vk_adnetwork_setting_section_disable_ads'
                    );


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // "opt out from internal notices." settings section
        // Блок "Отключить Ad Health и остальные уведомления"
        add_settings_section(
                    'vk_adnetwork_setting_section_disabled_notices',
                    // Отключить Ad Health и остальные уведомления
                    esc_html__( 'Disable Ad Health and other notices', 'vk-adnetwork' ),
                    [ $this, 'render_settings_section_disabled_notices' ],
                    $hook
            );
                    add_settings_field(
                            'vk-adnetwork-disabled-notices',
                            '',
                            [ $this, 'vk_adnetwork_render_settings_disabled_notices' ],
                            $hook,
                            'vk_adnetwork_setting_section_disabled_notices'
                    );


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // "Management" settings section
        // (тут ничего)
        add_settings_section(
            'vk_adnetwork_setting_section',
            esc_html__( 'Deleting plugin data', 'vk-adnetwork' ),
            [ $this, 'render_settings_section_callback' ],
            $hook
        );

            // only for main blog
            // Удалить все данные при деинсталляции
            if ( is_main_site( get_current_blog_id() ) ) {
                add_settings_field(
                    'vk-adnetwork-uninstall-delete-data',
                    // esc_html__( 'Delete data on uninstall', 'vk-adnetwork' ),
                                        '',
                    [ $this, 'vk_adnetwork_render_settings_uninstall_delete_data' ],
                    $hook,
                    'vk_adnetwork_setting_section'
                );
            }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // hook for additional settings from add-ons.
        do_action( 'vk-adnetwork-settings-init', $hook );
    }

    /**
     * Make sure ad admin can save options.
     * Add a filter on `admin_init` priority 20 to allow other modules/add-ons to add their options.
     * Filter option_page_capability_ with the appropriate slug in return to allow the Ad Admin user role to save these settings/options.
     */
    public function settings_capabilities() {
        $ad_admin_options = [ VK_ADNETWORK_SLUG ];
        /**
         * Filters all options that the Ad Admin Role should have access to.
         *
         * @param array $ad_admin_options Array with option names.
         */
        $ad_admin_options = apply_filters( 'vk-adnetwork-ad-admin-options', $ad_admin_options );
        foreach ( $ad_admin_options as $ad_admin_option ) {
            add_filter( 'option_page_capability_' . $ad_admin_option, function() {
                return VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' );
            } );
        }
    }

    /**
     * Render settings section
     */
    public function render_settings_section_callback() {
        // for whatever purpose there might come.
    }

    /**
     * Render "vk-adnetwork-creds" settings section
     */
    public function render_settings_section_vk_adnetwork_creds_callback() {
        // for whatever purpose there might come.
    }

    /**
     * Render vk_adnetwork_message section
     */
    public function render_vk_adnetwork_message() {
        echo wp_kses(
            '<div class="text settings-message"><img src="' . VK_ADNETWORK_BASE_URL . 'admin/assets/img/i.png" alt="(i)"> '
                . sanitize_text_field($_GET['message']) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            . '</div>',
            ['div' => ['class' => true], 'img' => ['src' => true, 'alt' => true]]
        );
    }

    /**
     * Options vk-adnetwork-creds
     */
    public function render_settings_vk_adnetwork_creds() {
        $options = VK_Adnetwork::get_instance()->options();

        // set the variables.
        $client_id      = $options['vk-adnetwork-creds']['client_id']     ?? '';
        $client_secret  = $options['vk-adnetwork-creds']['client_secret'] ?? '';
        $access_token   = $options['vk-adnetwork-creds']['access_token']  ?? '';
        $refresh_token  = $options['vk-adnetwork-creds']['refresh_token'] ?? '';
        $tokens_left    = $options['vk-adnetwork-creds']['tokens_left']   ?? '';
        $delete_tokens  = $options['vk-adnetwork-creds']['delete_tokens'] ?? '';

        // load the template.
        include VK_ADNETWORK_BASE_PATH . 'admin/views/settings/general/vk-adnetwork-creds.php';
    }

    /**
     * Render "Disable Ads" settings section
     */
    public function render_settings_section_disable_ads_callback() {
        // for whatever purpose there might come.
    }

    /**
     * Блок "Отключить Ad Health и остальные уведомления"
     */
    public function render_settings_section_disabled_notices() {
        // for whatever purpose there might come.
    }

    /**
     * Options to disable ads
     * Отключить рекламу (6 галочек: всю, 404, архив, вторичка, РСС, РЕСТ) -- оставили одну галочку
     * | 451 | vk-adnetwork | a:2:{s:12:"vk-adnetwork-disabled-ads";a:4:{s:8:"archives";s:1:"1";s:9:"secondary";s:1:"1";s:4:"feed";i:1;s:8:"rest-api";s:1:"1";}s:6:"ga-UID";s:0:"";} |
     */
    public function vk_adnetwork_render_settings_disable_ads() {
        $options = VK_Adnetwork::get_instance()->options();

        // set the variables.
        $disable_all       = isset( $options['vk-adnetwork-disabled-ads']['all'] ) ? 1 : 0;

        // load the template.
        include VK_ADNETWORK_BASE_PATH . 'admin/views/settings/general/vk-adnetwork-disable-ads.php';
    }

    /**
     *
     */
    public function vk_adnetwork_render_settings_disabled_notices() {
        $options = VK_Adnetwork::get_instance()->options();

        // set the variables.
        $disable_notices = ( ! empty( $options['disable-notices'] ) ) ? 1 : 0;

        // load the template.
        include VK_ADNETWORK_BASE_PATH . 'admin/views/settings/general/vk-adnetwork-disable-notices.php';
    }

    /**
     * Render setting to hide ads from logged in users
     * Скрыть объявления для пользователей этих ролей
     * | 451 | vk-adnetwork | a:3:{s:12:"vk-adnetwork-disabled-ads";a:4:{...}s:18:"vk-adnetwork-hide-for-user-role";a:2:{i:0;s:13:"administrator";i:1;s:6:"editor";}s:6:"ga-UID";s:0:"";} |
     */
    public function vk_adnetwork_render_settings_hide_for_users() {
        $options = VK_Adnetwork::get_instance()->options();
        if ( isset( $options['vk-adnetwork-hide-for-user-role'] ) ) {
            $hide_for_roles = VK_Adnetwork_Utils::vk_adnetwork_maybe_translate_cap_to_role( $options['vk-adnetwork-hide-for-user-role'] );
        } else {
            $hide_for_roles = [];
        }

        global $wp_roles;
        // $roles = $wp_roles->get_names();
        $roles = [
            'administrator' => esc_html__( 'Administrator', 'vk-adnetwork' ),
            'editor'        => esc_html__( 'Editor', 'vk-adnetwork' ),
            'author'        => esc_html__( 'Author', 'vk-adnetwork' ),
        ];
        include VK_ADNETWORK_BASE_PATH . 'admin/views/settings/general/vk-adnetwork-hide-for-user-role.php';
    }

    /**
     * Render setting 'Delete data on uninstall"
     * Удалить все данные при деинсталляции
     * | 451 | vk-adnetwork | a:5:{s:12:"vk-adnetwork-disabled-ads";...;s:21:"vk-adnetwork-uninstall-delete-data";s:1:"1";s:6:"ga-UID";s:0:"";} |
     */
    public function vk_adnetwork_render_settings_uninstall_delete_data() {
        $options = VK_Adnetwork::get_instance()->options();
        $enabled = ! empty( $options['vk-adnetwork-uninstall-delete-data'] );

        include VK_ADNETWORK_BASE_PATH . 'admin/views/settings/general/vk-adnetwork-uninstall-delete-data.php';
    }

    /**
     * Sanitize plugin settings
     *
     * @param array $options all the options.
     *
     * @return array sanitized options.
     */
    public function sanitize_settings( $options ) {

        // sanitize whatever option one wants to sanitize.
        if ( isset( $options['front-prefix'] ) ) {
            $options['front-prefix'] = VK_Adnetwork_Plugin::get_instance()->sanitize_frontend_prefix(
                $options['front-prefix'],
                VK_Adnetwork_Plugin::DEFAULT_FRONTEND_PREFIX
            );
        }

        $options = apply_filters( 'vk-adnetwork-sanitize-settings', $options );

        // check if editors can edit ads now and set the rights
        // else, remove that right.
        $editor_role = get_role( 'editor' );
        if ( null === $editor_role ) {
            return $options;
        }
        if ( isset( $options['editors-manage-ads'] ) && $options['editors-manage-ads'] ) {
            $editor_role->add_cap( 'vk_adnetwork_see_interface' );
            $editor_role->add_cap( 'vk_adnetwork_edit_ads' );
            $editor_role->add_cap( 'vk_adnetwork_manage_placements' );
            $editor_role->add_cap( 'vk_adnetwork_place_ads' );
        } else {
            $editor_role->remove_cap( 'vk_adnetwork_see_interface' );
            $editor_role->remove_cap( 'vk_adnetwork_edit_ads' );
            $editor_role->remove_cap( 'vk_adnetwork_manage_placements' );
            $editor_role->remove_cap( 'vk_adnetwork_place_ads' );
        }

        if ( isset( $options['content-injection-everywhere'] ) ) {
            if ( '0' === $options['content-injection-everywhere'] ) {
                unset( $options['content-injection-everywhere'] );
            } elseif ( $options['content-injection-everywhere'] === 'true' || $options['content-injection-everywhere'] <= - 1 ) {
                // Note: the option may be already set 'true' during import.
                $options['content-injection-everywhere'] = 'true';
            } else {
                $options['content-injection-everywhere'] = absint( $options['content-injection-everywhere'] );
            }
        }

        return $options;
    }

}
