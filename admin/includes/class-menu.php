<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class VK_Adnetwork_Admin_Menu
 */
class VK_Adnetwork_Admin_Menu {
    /**
     * Instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Slug of the ad group page
     *
     * @var      string
     */
    protected $ad_group_hook_suffix = null;
    private $post_type;
    public string $plugin_slug;

    /**
     * VK_Adnetwork_Admin_Menu constructor.
     */
    private function __construct() {
        // add menu items.
        add_action( 'admin_menu', [ $this, 'add_plugin_admin_menu' ] );
        add_action( 'admin_head', [ $this, 'highlight_menu_item' ] );

        $this->plugin_slug = VK_Adnetwork::get_instance()->get_plugin_slug();
        $this->post_type   = constant( 'VK_Adnetwork::POST_TYPE_SLUG' );
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
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        // get number of ads including those in trash.
        $has_ads = VK_Adnetwork::get_number_of_ads( [ 'any', 'trash' ] );

        // get number of Ad Health notices.
        $notices = VK_Adnetwork_Ad_Health_Notices::get_number_of_notices();
        // string for Ad Health notice number.
        $notice_alert = '&nbsp;<span class="update-plugins count-' . $notices . '"><span class="update-count">' . $notices . '</span></span>';

        $options = VK_Adnetwork::get_instance()->options();
        $newuser = empty($options['vk-adnetwork-creds']['client_id']) || empty($options['vk-adnetwork-creds']['client_secret']);

        if ($newuser) {

            add_menu_page(
                // Авторизация: это в черном меню слева (под)заголовок! новичкам кастомизация!
                esc_html__('Authorization', 'vk-adnetwork'),
                'VK AdNetwork',
                VK_Adnetwork_Plugin::user_cap('vk_adnetwork_see_interface'),
                $this->plugin_slug . '-settings',
                [$this, 'display_plugin_settings_page'],
                VK_Adnetwork_Plugin::get_icon_svg(),
                '58.74'
            );

        }else{ // новенькому ни хера этого НЕ НАДО!

            // use the overview page only when there is an ad already.
            // используйте обзорную страницу только в том случае, если объявление уже есть.
            // if ($has_ads) { первая (автоматическая) реклама еще не появилась -- но вот вот появится -- поэтому это условие убираем!
                add_menu_page(
                    esc_html__('Dashboard', 'vk-adnetwork'),
                    'VK AdNetwork',
                    VK_Adnetwork_Plugin::user_cap('vk_adnetwork_see_interface'),
                    $this->plugin_slug,
                    [$this, 'display_overview_page'],
                    VK_Adnetwork_Plugin::get_icon_svg(),
                    '58.74'
                );
                // the main menu entry automatically creates a submenu entry with the title "VK AdNetwork"
                // to show another title, we needed to create a submenu entry that uses the same page menu slug as the parent menu item
                // в главном меню автоматически создается запись подменю с заголовком "VK AdNetwork".
                // чтобы отобразить другой заголовок, нам нужно было создать элемент подменю, который использует тот же элемент меню страницы, что и родительский пункт меню
                add_submenu_page(
                    $this->plugin_slug,
                    esc_html__('Dashboard', 'vk-adnetwork'),
                    esc_html__('Dashboard', 'vk-adnetwork'),
                    VK_Adnetwork_Plugin::user_cap('vk_adnetwork_edit_ads'),
                    $this->plugin_slug
                );
            // }

            // forward Ads link to new-ad page when there is no ad existing yet.
            // the target to post-new.php needs the extra "new" or any other attribute, since the original add-ad link was removed by CSS using the exact href attribute as a selector.
            // $target = (!$has_ads) ? 'post-new.php?post_type=' . VK_Adnetwork::POST_TYPE_SLUG . '&new=new' : 'edit.php?post_type=' . VK_Adnetwork::POST_TYPE_SLUG;
            $target = 'edit.php?post_type=' . VK_Adnetwork::POST_TYPE_SLUG;
            add_submenu_page(
                $this->plugin_slug,
                esc_html__('Ads', 'vk-adnetwork'),
                esc_html__('Ads', 'vk-adnetwork'),
                VK_Adnetwork_Plugin::user_cap('vk_adnetwork_edit_ads'),
                $target
            );

            // display the main overview page as second item when we don’t have ads yet.
            // отображать главную обзорную страницу в качестве второго элемента, когда у нас еще нет рекламы
            // if (!$has_ads) { -- первая (автоматическая) реклама еще не появилась -- но вот вот появится -- поэтому это условие убираем!

            // hidden by css; not placed in 'options.php' in order to highlight the correct item, see the 'highlight_menu_item()'.
            if (!current_user_can('edit_posts')) {
                add_submenu_page(
                    $this->plugin_slug,
                    // Добавить новый рекламный блок
                    esc_html__('Add New Ad', 'vk-adnetwork'),
                    // Новый рекламный блок
                    esc_html__('New Ad', 'vk-adnetwork'),
                    VK_Adnetwork_Plugin::user_cap('vk_adnetwork_edit_ads'),
                    'post-new.php?post_type=' . VK_Adnetwork::POST_TYPE_SLUG
                );
            }

            // add settings page. VK_Adnetwork_Admin::get_instance()->plugin_screen_hook_suffix =
            add_submenu_page(
                $this->plugin_slug,
                esc_html__( 'Settings', 'vk-adnetwork' ),                      // не вижу где это (
                esc_html__( 'Settings', 'vk-adnetwork' ),
                VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' ),
                $this->plugin_slug . '-settings',
                [ $this, 'display_plugin_settings_page' ]
            );

            // VK_Adnetwork_Admin::get_instance()->plugin_screen_hook_suffix =
            add_submenu_page(
                $this->plugin_slug,
                esc_html__( 'Support', 'vk-adnetwork' ),                      // не вижу где это (
                esc_html__( 'Support', 'vk-adnetwork' ),
                VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_see_interface' ),
                $this->plugin_slug . '-support',
                [ $this, 'display_plugin_support_page' ]
            );

        }

        /**
         * Since we forward the support link to the settings page, we need to add the menu item manually
         * could break if WordPress changes the API at one point, but it didn’t do that for many years
         */
        global $submenu;
        if ( !$newuser && current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' ) ) ) {
            // we have to mute the phpcs warning about overriding superglobals since WordPress does not offer a better way to manipulate menu links.
            // phpcs:ignore
            // manipulate the title of the overview submenu page and add error count.
            if ( $has_ads ) {
                // we have to mute the phpcs warning about overriding superglobals since WordPress does not offer a better way to manipulate menu links.
                // phpcs:ignore
                $submenu['vk-adnetwork'][0][0] .= $notice_alert;
            } else {
                // we have to mute the phpcs warning about overriding superglobals since WordPress does not offer a better way to manipulate menu links.
                // phpcs:ignore
                $submenu['vk-adnetwork'][1][0] .= $notice_alert;
            }
        }

        /**
         * Allows extensions to insert sub menu pages.
         *
         * @since untagged Added the `$hidden_page_slug` parameter.
         *
         * @param string $plugin_slug      The slug slug used to add a visible page.
         * @param string $hidden_page_slug The slug slug used to add a hidden page.
         */
        do_action( 'vk-adnetwork-submenu-pages', $this->plugin_slug, 'vk_adnetwork_hidden_page_slug' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
    }

    /**
     * Highlights the 'VK AdNetwork->Ads' item in the menu when an ad edit page is open
     *
     * @see the 'parent_file' and the 'submenu_file' filters for reference
     */
    public function highlight_menu_item() {
        global $parent_file, $submenu_file, $post_type;
        if ( $post_type === $this->post_type ) {
            // we have to mute the phpcs warning about overriding superglobals since WordPress does not offer a better way to manipulate menu links.
            // phpcs:ignore
            $parent_file  = $this->plugin_slug;
            // phpcs:ignore
            $submenu_file = 'edit.php?post_type=' . $this->post_type;
        }
    }

    /**
     * Render the overview page
     *
     * @since    1.2.2
     */
    public function display_overview_page() {

        include VK_ADNETWORK_BASE_PATH . 'admin/views/overview.php';
    }

    /**
     * Render the settings page
     *
     * @since    1.0.0
     */
    public function display_plugin_settings_page() {
        $options = VK_Adnetwork::get_instance()->options();
        $newuser = empty($options['vk-adnetwork-creds']['client_id']) || empty($options['vk-adnetwork-creds']['client_secret']);
        // Регистрация в VK AdNetwork
        $title2_settings_page = $newuser
            ? esc_html__('Registration in VK AdNetwork', 'vk-adnetwork')
            : esc_html__('Settings', 'vk-adnetwork');
        include VK_ADNETWORK_BASE_PATH . 'admin/views/settings.php';
    }

    public function display_plugin_support_page() {
        include VK_ADNETWORK_BASE_PATH . 'admin/views/support.php';
    }

}
