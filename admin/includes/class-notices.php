<?php

/**
 * Container class for admin notices
 *
 * @package WordPress
 * @subpackage VK AdNetwork Plugin
 */
class VK_Adnetwork_Admin_Notices {

    /**
     * Maximum number of notices to show at once
     */
    const MAX_NOTICES = 2;

    /**
     * Instance of this class
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Options
     *
     * @var    array
     */
    protected $options;

    /**
     * Notices to be displayed
     *
     * @var    array
     */
    public $notices = [];

    /**
     * Plugin class
     *
     * @var VK_Adnetwork_Plugin
     */
    private $plugin;

    /**
     * VK_Adnetwork_Admin_Notices constructor to load notices
     */
    public function __construct() {
        $this->plugin = VK_Adnetwork_Plugin::get_instance();
        // load notices.
        $this->load_notices();

    }

    /**
     * Return an instance of this class.
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // if the single instance hasn't been set, set it now.
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Load admin notices
     */
    public function load_notices() {

        $options        = $this->options();
        $plugin_options = $this->plugin->options();

        // load notices from queue.
        $this->notices  = $options['queue'] ?? [];
        $notices_before = $this->notices;

        // don’t check non-critical notices if they are disabled.
        if ( ! isset( $plugin_options['disable-notices'] ) ) {
            // check other notices.
            $this->check_notices();
        }

        // register notices in db so they get displayed until closed for good.
        if ( $this->notices !== $notices_before ) {
            $this->add_to_queue( $this->notices );
        }
    }

    /**
     * Update version number to latest one
     */
    public function update_version_number() {

        $internal_options = $this->plugin->internal_options();
        $new_options      = $internal_options; // in case we udpate options here.

        $new_options['version'] = VK_ADNETWORK_VERSION;

        // update version numbers.
        if ( $internal_options !== $new_options ) {
            $this->plugin->update_internal_options( $new_options );
        }
    }

    function add_del_notice($notice, $ifadd, $ifdel = true) {
        $options = $this->options();
        $closed  = $options['closed'] ?? [];
        $queue   = $options['queue'] ?? [];
        if ( $ifadd ) { # ! in_array( $notice, $queue, true ) &&
            if ( ! isset( $closed[$notice] ) )
                // $this->notices[] = $notice; любой нотис исключает остальные ( nl_intro | nl_moderation | nl_moderation_done | nl_other_issues )
                $this->notices = [$notice];
            else
                $this->notices = []; // вот у нас ЭТОТ нотис закрыт крестиком -- но остальные тоже же нафик не нужны теперь?
            return true;
        }elseif ( $ifdel ) { // т.е. или дополнительное условие для удаления нотиса (или просто отрицание $ifadd, если ничего не указано)
            $key = array_search( $notice, $this->notices, true );
            if ( false !== $key ) {
                unset( $this->notices[ $key ] );
            }
            return false;
        }
    }

    /**
     * Check various notices conditions
     */
    public function check_notices() {
        $number_of_ads = 0;
        // needed error handling due to a weird bug in the piklist plugin.
        try {
            $number_of_ads = VK_Adnetwork::get_number_of_ads();
        } catch ( Exception $e ) {
            // no need to catch anything since we just use TRY/CATCH to prevent an issue caused by another plugin.
        }

        // register intro message. // Добро пожаловать в VK AdNetwork! ... // Внести ключи API
        // А) нет реклам (корзину не считаем) <И>
        // Б) wp_options->'vk-adnetwork-notices' === a:0:{} т.е. никаких нотисов еще не было в природе <И>
        // В) пусто в полях client_id и/или client_secret (т.е. в тексте есть -- Внести ключи API)
        $options = VK_Adnetwork::get_instance()->options();
        $newuser = empty($options['vk-adnetwork-creds']['client_id']) || empty($options['vk-adnetwork-creds']['client_secret']);
        if ($this->add_del_notice('nl_intro', ! $number_of_ads && [] === $this->options() && $newuser, $number_of_ads || ! $newuser))
            // если повесили nl_intro, то не имеет смысла смотреть на модерации
            return;

        // посмотрим что с модерацией в майТаргете:
        // vk_adnetwork_group_pads: сохраним статусы и проблемы площадки, чтобы не запрашивать на каждой странице их снова [status delivery issues]
        // нету статуса модерации или он старше 10 мин! (и есть групп-ид)
        if ((!isset($options['moderation']) || $options['moderation']['time'] < time() - 600) && isset($options['group_id'])) { //
            // тогда перезапросим https://ads.vk.com/api/v2/group_pads/1678488.json?fields=id,status,issues,delivery
            $items = VK_Adnetwork_Utils::vk_adnetwork_group_pads($options['group_id']);
            $options['moderation']['issues'] = $items['group_pads'][$options['group_id']]['issues'] ?? '';
        }

        $issues = $options['moderation']['issues'] ?? '';

        // Модерация пройдена? (и уходим -- нет смысла смотреть дальше)
        // ??: && $moderation['status'] === 'active' && $moderation['delivery'] === 'delivering'
        if ($this->add_del_notice('nl_moderation_done', $issues === []))
            return;
        // площадка на модерации? -- повесим нотис "Площадка на модерации" (и уходим -- нет смысла смотреть дальше)
        if ($this->add_del_notice('nl_moderation', isset($issues['GROUP_PAD_ON_MODERATION'])))
            return;
        // любые прочие issues: GROUP_PAD_BANNED, GROUP_PAD_ARCHIVED, GROUP_PAD_MODERATION_REASON_BAD_CONTENT, GROUP_PAD_MODERATION_REASON_PARTNER_IS_NOT_OWNER, ...
        // добавил оба условия -- $ifadd, $ifdel т.к. м.б. из-за этого пустые нотисы лезут?
        // Типа плохой ответ от МТ -- пустая строка = пустой нотис?
        $this->add_del_notice('nl_other_issues', is_array($issues) && ! empty($issues), ! is_array($issues) || empty($issues));
    }

    /**
     * Add update notices to the queue of all notices that still needs to be closed
     * Добавляйте уведомления об обновлениях в очередь всех уведомлений, которые все еще необходимо закрыть
     * @param mixed $notices one or more notices to be added to the queue.
     * об одном или нескольких уведомлениях, которые будут добавлены в очередь.
     *
     * @since 1.5.3
     */
    public function add_to_queue( $notices = 0 ) {
        if ( ! $notices ) {
            return;
        }

        // get queue from options.
        $options = $this->options();
        $queue   = $options['queue'] ?? [];

        if ( is_array( $notices ) ) {
            $queue = array_merge( $queue, $notices );
        } else {
            $queue[] = $notices;
        }

        // remove possible duplicated.
        $queue = array_unique( $queue );

        // update db.
        $options['queue'] = $queue;
        $this->update_options( $options );
    }

    /**
     * Remove update notice from queue
     *  move notice into "closed"
     *
     * @param string $notice notice to be removed from the queue.
     *
     * @since 1.5.3
     */
    public function remove_from_queue( $notice ) {
        if ( ! isset( $notice ) ) {
            return;
        }

        // get queue from options.
        $options        = $this->options();
        $options_before = $options;
        if ( ! isset( $options['queue'] ) ) {
            return;
        }
        $queue  = (array) $options['queue'];
        $closed = $options['closed'] ?? [];
        $paused = $options['paused'] ?? [];

        $key = array_search( $notice, $queue, true );
        if ( false !== $key ) {
            unset( $queue[ $key ] );
            // close message with timestamp.
        }
        // don’t close again twice.
        if ( ! isset( $closed[ $notice ] ) ) {
            $closed[ $notice ] = time();
        }
        // remove from pause.
        if ( isset( $paused[ $notice ] ) ) {
            unset( $paused[ $notice ] );
        }

        // update db.
        $options['queue']  = $queue;
        $options['closed'] = $closed;
        $options['paused'] = $paused;

        // only update if changed.
        if ( $options_before !== $options ) {
            $this->update_options( $options );
            // update already registered notices.
            $this->load_notices();
        }
    }

    /**
     *  Hide any notice for a given time
     *  move notice into "paused" with notice as key and timestamp as value
     *
     * @param string $notice notice to be paused.
     */
    public function hide_notice( $notice ) {
        if ( ! isset( $notice ) ) {
            return;
        }

        // get queue from options.
        $options        = $this->options();
        $options_before = $options;
        if ( ! isset( $options['queue'] ) ) {
            return;
        }
        $queue  = (array) $options['queue'];
        $paused = $options['paused'] ?? [];

        $key = array_search( $notice, $queue, true );
        if ( false !== $key ) {
            unset( $queue[ $key ] );
        }
        // close message with timestamp in 7 days
        // don’t close again twice.
        if ( ! isset( $paused[ $notice ] ) ) {
            $paused[ $notice ] = time() + WEEK_IN_SECONDS;
        }

        // update db.
        $options['queue']  = $queue;
        $options['paused'] = $paused;

        // only update if changed.
        if ( $options_before !== $options ) {
            $this->update_options( $options );
            // update already registered notices.
            $this->load_notices();
        }
    }

    /**
     * Display notices
     */
    public function display_notices() {

        if ( defined( 'DOING_AJAX' ) ) {
            return;
        }

        if ( [] === $this->notices ) {
            return;
        }

        // hide the welcome panel on the ad edit page
        $screen = get_current_screen();
        if ( isset( $screen->id ) && $screen->id === 'vk_adnetwork' ) {     // ad edit page. /wp-admin/post.php?post=480&action=edit
            // на странице редактирования объявления НЕ показываем welcome_panel (// Добро пожаловать в VK AdNetwork! ... // Внести ключи API)
            $intro_key = array_search( 'nl_intro', $this->notices, true );
            if ( $intro_key !== false ) {
                unset( $this->notices[ $intro_key ] );
            }
        }

        // load notices.
        include VK_ADNETWORK_BASE_PATH . 'admin/includes/notices.php';

        // iterate through notices.
        $count = 0;
        foreach ( $this->notices as $_notice ) {

            if ( isset( $vk_adnetwork_admin_notices[ $_notice ] ) ) {
                $notice = $vk_adnetwork_admin_notices[ $_notice ];
                $text   = $vk_adnetwork_admin_notices[ $_notice ]['text'];
                $type   = $vk_adnetwork_admin_notices[$_notice]['type'] ?? '';
            } else {
                continue;
            }

            // don’t display non-global notices on other than plugin related pages.
            if ( ( ! isset( $vk_adnetwork_admin_notices[ $_notice ]['global'] ) || ! $vk_adnetwork_admin_notices[ $_notice ]['global'] )
                 && ! VK_Adnetwork_Admin::screen_belongs_to_vk_adnetwork() ) {
                continue;
            }

            // don't display license nag if VK_ADNETWORK_SUPPRESS_PLUGIN_ERROR_NOTICES is defined.
            if ( defined( 'VK_ADNETWORK_SUPPRESS_PLUGIN_ERROR_NOTICES' ) && 'plugin_error' === $vk_adnetwork_admin_notices[ $_notice ]['type'] ) {
                continue;
            }

            switch ( $type ) {
                case 'info':
                    include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/info.php';
                    break;
                case 'plugin_error':
                    include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/plugin_error.php';
                    break;
                case 'promo':
                    include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/promo.php';
                    break;
                default:
                    include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/error.php';
            }

            if ( self::MAX_NOTICES === ++ $count ) {
                break;
            }
        }
    }

    /**
     * Return notices options
     *
     * @return array $options
     */
    public function options() {
        if ( ! isset( $this->options ) ) {
            $this->options = get_option( VK_ADNETWORK_SLUG . '-notices', [] );
        }

        return $this->options;
    }

    /**
     * Update notices options
     *
     * @param array $options new options.
     */
    public function update_options( array $options ) {
        // do not allow to clear options.
        if ( [] === $options ) {
            return;
        }

        $this->options = $options;
        update_option( VK_ADNETWORK_SLUG . '-notices', $options );
    }

    /**
     * Create the content of a welcome panel like WordPress core does
     */
    public function get_welcome_panel() {
        ob_start();
        include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/welcome-panel.php';
        return ob_get_clean();
    }

    /**
     * Create the content of a moderation panel like WordPress core does
     */
    public function get_moderation_panel() {
        ob_start();
        include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/moderation-panel.php';
        return ob_get_clean();
    }

    /**
     * Create the content of a moderation OK panel like WordPress core does
     */
    public function get_moderation_done_panel() {
        ob_start();
        include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/moderation-done-panel.php';
        return ob_get_clean();
    }

    /**
     * Create the content of a moderation OK panel like WordPress core does
     */
    public function get_other_issues_panel() {
        $options = VK_Adnetwork::get_instance()->options();
        $issues = array_keys($options['moderation']['issues'] ?? []);
        if (!$issues) return;
        ob_start();
        include VK_ADNETWORK_BASE_PATH . 'admin/views/notices/other-issues-panel.php';
        return ob_get_clean();
    }
}
