<?php

/**
 * VK AdNetwork.
 *
 * @package   VK_Adnetwork
 * @license   GPL-2.0+
 * @link      https://vk.com
 * @copyright 2023 VK
 */

/**
 * This class is used to bundle all ajax callbacks
 *
 * @package VK_Adnetwork_Ajax_Callbacks
 */
class VK_Adnetwork_Ad_Ajax_Callbacks {

    /**
     * VK_Adnetwork_Ad_Ajax_Callbacks constructor.
     */
    public function __construct() {

        // admin only!
        add_action( 'wp_ajax_vk_adnetwork_load_ad_parameters_metabox', [ $this, 'vk_adnetwork_load_ad_parameters_metabox' ] );

        add_action( 'wp_ajax_vk_adnetwork-close-notice', [ $this, 'close_notice' ] );
        add_action( 'wp_ajax_vk_adnetwork-hide-notice', [ $this, 'hide_notice' ] );

        add_action( 'wp_ajax_vk_adnetwork-ad-injection-content', [ $this, 'inject_placement' ] );
        add_action( 'wp_ajax_vk_adnetwork-save-hide-wizard-state', [ $this, 'save_wizard_state' ] );
        add_action( 'wp_ajax_vk_adnetwork-ad-health-notice-display', [ $this, 'ad_health_notice_display' ] );
        add_action( 'wp_ajax_vk_adnetwork-ad-health-notice-push-adminui', [ $this, 'ad_health_notice_push' ] );
        add_action( 'wp_ajax_vk_adnetwork-ad-health-notice-hide', [ $this, 'ad_health_notice_hide' ] );
        add_action( 'wp_ajax_vk_adnetwork-ad-health-notice-unignore', [ $this, 'ad_health_notice_unignore' ] );
        add_action( 'wp_ajax_vk_adnetwork-ad-health-notice-solved', [ $this, 'ad_health_notice_solved' ] );
        add_action( 'wp_ajax_vk_adnetwork-placements-allowed-ads', [ $this, 'get_allowed_ads_for_placement_type' ] );

    }

    /**
     * Load content of the ad parameter metabox
     *
     * @since 1.0.0
     */
    public function vk_adnetwork_load_ad_parameters_metabox() {

        check_ajax_referer( 'vk-adnetwork-admin-ajax-nonce', 'nonce' );
        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) ) ) {
            return;
        }

        $types       = VK_Adnetwork::get_instance()->ad_types;
        $type_string = sanitize_text_field($_REQUEST['ad_type']);
        $ad_id       = absint( $_REQUEST['ad_id'] );
        if ( empty( $ad_id ) ) {
            die();
        }

        $ad = new VK_Adnetwork_Ad( $ad_id );

        if ( ! empty( $types[ $type_string ] ) && method_exists( $types[ $type_string ], 'render_parameters' ) ) {
            $type = $types[ $type_string ];
            $type->render_parameters( $ad );


            include VK_ADNETWORK_BASE_PATH . 'admin/views/ad-parameters-size.php'; // Размер ширина [0] px высота [0] px [_] зарезервировать это пространство

            // set the ad type attribute if empty
            if ( ! isset( $ad->type ) ) {
                $ad->type = $type_string;
            }

            // extend the AJAX-loaded parameters form by ad type
            if ( isset( $types[ $type_string ] ) ) {
                do_action( "vk-adnetwork-ad-params-after-$type_string", $ad, $types );
            }
        }

        die();

    }

    /**
     * Close a notice for good
     *
     * @since 1.5.3
     */
    public function close_notice() {

        check_ajax_referer( 'vk-adnetwork-admin-ajax-nonce', 'nonce' );

        if (
            ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' ) )
            || empty( $_REQUEST['notice'] )
        ) {
            die();
        }

        VK_Adnetwork_Admin_Notices::get_instance()->remove_from_queue( sanitize_text_field($_REQUEST['notice']) );
        if ( isset( $_REQUEST['redirect'] ) ) {
            wp_safe_redirect( esc_url( wp_sanitize_redirect( $_REQUEST['redirect'] ) ) ); // пздц!
            exit();
        }
        die();
    }

        /**
         * Hide a notice for some time (7 days right now)
         *
         * @since 1.8.17
         */
    public function hide_notice() {

        check_ajax_referer( 'vk-adnetwork-admin-ajax-nonce', 'nonce' );

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' ) )
        || empty( $_POST['notice'] )
        ) {
            die();
        }

        VK_Adnetwork_Admin_Notices::get_instance()->hide_notice( sanitize_text_field( $_POST['notice'] ) );
        die();
    }

    /**
     * Inject an ad and a placement
     *
     * @since 1.7.3
     */
    public function inject_placement() {

        check_ajax_referer( 'vk-adnetwork-admin-ajax-nonce', 'nonce' );

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) ) ) {
            die();
        }

        $ad_id = absint( $_REQUEST['ad_id'] );
        if ( empty( $ad_id ) ) {
            die(); }

        // create new placement.
        $placements = VK_Adnetwork::get_instance()->get_model()->get_ad_placements_array();

        $type = sanitize_text_field( $_REQUEST['placement_type'] );

        $item = 'ad_' . $ad_id;

        $options = [];

        // check type.
        $placement_types = VK_Adnetwork_Placements::get_placement_types();
        if ( ! isset( $placement_types[ $type ] ) ) {
            die();
        }

        $title = $placement_types[ $type ]['title'];

        $new_placement = [
            'type' => $type,
            'item' => $item,
            'name' => $title,
        ];

        $index = isset( $_REQUEST['options']['index'] ) ? absint( $_REQUEST['options']['index'] ) : '';
        // set content specific options.
        if ( 'post_content' === $type ) {
            $new_placement['options'] = [
                'position' => 'after',
                'index'    => $index ?: 1,
                'tag'      => 'p',
            ];
        }

        $slug = VK_Adnetwork_Placements::save_new_placement( $new_placement );

        $ad = new VK_Adnetwork_Ad( $ad_id );
        if ($ad) {
            // тут сохраняем сразу при нажатии на одну из кнопок: [top], [content 1], [bottom], [short/php] - 3 пробела
            // а яваскриптом добавим в поле Заметки [ type index ] (с 1 пробелом) - чтобы сохранилось по кнопке [Обновить]
            // т.е. так или иначе в объявлении в description сохранится местоположение (с 3 или 1 пробелом)
//            $tti = " [ $type   $index ]";
//            $ad->description = strpos($ad->description, '[') && strpos($ad->description, ']')
//                ? preg_replace('/\[[^\]]*]/', $tti, $ad->description, 1)
//                : $ad->description . " $tti";
            $ad->placement_type = $type;
            $ad->placement_p    = $index ?: '';
            $ad->save();
            clean_post_cache( $ad->id );
        }

        // return potential slug.
        echo esc_attr( $slug );

        die();
    }

    /**
     * Save ad wizard state for each user individually
     *
     * @since 1.7.4
     */
    public function save_wizard_state() {

        check_ajax_referer( 'vk-adnetwork-admin-ajax-nonce', 'nonce' );

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) ) ) {
            return;
        }

        $state = ( isset( $_REQUEST['hide_wizard'] ) && 'true' === $_REQUEST['hide_wizard'] ) ? 'true' : 'false';

        // get current user.
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            die();
        }

        update_user_meta( $user_id, 'vk-adnetwork-hide-wizard', $state );

        die();
    }

    /**
     * Display list of Ad Health notices
     */
    public function ad_health_notice_display() {

        check_ajax_referer( 'vk-adnetwork-admin-ajax-nonce', 'nonce' );

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' ) ) ) {
            return;
        }

        VK_Adnetwork_Ad_Health_Notices::get_instance()->render_widget();
        die();
    }

    /**
     * Push an Ad Health notice to the queue
     */
    public function ad_health_notice_push() {

        check_ajax_referer( 'vk-adnetwork-admin-ajax-nonce', 'nonce' );

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' ) ) ) {
            return;
        }

        $key  = sanitize_text_field($_REQUEST['key'] ?? '');
        $attr = []; // instead VK_Adnetwork_Utils::vk_adnetwork_sanitize_array()
        if ( ! empty( $_REQUEST['attr'] ) && is_array( $_REQUEST['attr'] ) ) {
            foreach (['closed', 'append_key', 'append_text', 'text', 'ad_id'] as $keyattr) {
                if (isset($_REQUEST['attr'][$keyattr]))
                    $attr[$keyattr] = sanitize_text_field($_REQUEST['attr'][$keyattr]);
            }
        }

        // update or new entry?
        if ( isset( $attr['mode'] ) && 'update' === $attr['mode'] ) {
            VK_Adnetwork_Ad_Health_Notices::get_instance()->update( $key, $attr ); // append_text, closed
        } else {
            VK_Adnetwork_Ad_Health_Notices::get_instance()->add( $key, $attr ); // append_key, append_text, ad_id, text
        }

        die();
    }

    /**
     * Hide Ad Health notice
     */
    public function ad_health_notice_hide() {
        check_ajax_referer( 'vk-adnetwork-admin-ajax-nonce', 'nonce' );

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' ) ) ) {
            return;
        }

        $notice_key = sanitize_text_field($_REQUEST['notice'] ?? '');

        VK_Adnetwork_Ad_Health_Notices::get_instance()->hide( $notice_key );
        die();
    }

    /**
     * Show all ignored notices of a given type
     */
    public function ad_health_notice_unignore() {
        check_ajax_referer( 'vk-adnetwork-admin-ajax-nonce', 'nonce' );

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' ) ) ) {
            return;
        }

        VK_Adnetwork_Ad_Health_Notices::get_instance()->unignore();
        die();
    }

    /**
     * Get allowed ads per placement.
     *
     * @return void
     */
    public function get_allowed_ads_for_placement_type() {
        check_ajax_referer( sanitize_text_field( $_POST['action'] ) );

        wp_send_json_success( [
            'items' => array_filter(
                VK_Adnetwork_Placements::get_items_for_placement( sanitize_text_field( $_POST['placement_type'] ) ),
                static function( $items_group ) {
                    return ! empty( $items_group['items'] );
                }
            ),
        ] );
    }
}
