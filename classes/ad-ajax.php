<?php

/**
 * Provide public ajax interface.
 *
 * @since 1.5.0
 */
class VK_Adnetwork_Ajax {

    /**
     * VK_Adnetwork_Ajax constructor.
     */
    private function __construct() {

        add_action( 'wp_ajax_vk_adnetwork-ad-health-notice-push', [ $this, 'ad_health_notice_push' ] );
        add_action( 'wp_ajax_nopriv_vk_adnetwork-ad-health-notice-push', [ $this, 'ad_health_notice_push' ] );
        add_action( 'wp_ajax_vk_adnetwork-ad-frontend-notice-update', [ $this, 'frontend_notice_update' ] );
    }

    /**
     * Instance of VK_Adnetwork_Ajax
     *
     * @var $instance
     */
    private static $instance;

    /**
     * Instance getter
     *
     * @return VK_Adnetwork_Ajax
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Push an Ad Health notice to the queue in the backend
     */
    public function ad_health_notice_push() {

        check_ajax_referer( 'vk-adnetwork-ad-health-ajax-nonce', 'nonce' );

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) ) ) {
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

        // Update or new entry?
        if ( isset( $attr['mode'] ) && 'update' === $attr['mode'] ) {
            VK_Adnetwork_Ad_Health_Notices::get_instance()->update( $key, $attr ); // append_text, closed
        } else {
            VK_Adnetwork_Ad_Health_Notices::get_instance()->add( $key, $attr ); // append_key, append_text, ad_id, text
        }

        die();
    }

    /**
     * Update frontend notice array
     */
    public function frontend_notice_update() {

        check_ajax_referer( 'vk-adnetwork-frontend-notice-nonce', 'nonce' );

        if ( ! current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) ) ) {
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
        // Update or new entry?
        if ( isset( $attr['mode'] ) && 'update' === $attr['mode'] ) {
            die();
            // VK_Adnetwork_Frontend_Notices::get_instance()->update( $key, $attr ); // append_text, closed
        } else {
            VK_Adnetwork_Frontend_Notices::get_instance()->update( $key, $attr ); // append_key, append_text, ad_id, text
        }

        die();
    }

    /**
     * Check if AJAX ad can be displayed, with consent information sent in request.
     * Проверьте, может ли отображаться реклама AJAX, отправив в запросе информацию о согласии.
     *
     * @param bool            $can_display Whether this ad can be displayed.
     * @param VK_Adnetwork_Ad $ad          The ad object.
     *
     * @return bool
     */
    public function can_display_by_consent( $can_display, $ad ) {
        // already false, honor this.
        if ( ! $can_display ) {
            return $can_display;
        }

        // If consent is overridden for the ad.
        if ( ! empty( $ad->options()['privacy']['ignore-consent'] ) ) {
            return true;
        }

        return true;
    }
}
