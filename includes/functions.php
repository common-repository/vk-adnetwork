<?php
defined('ABSPATH') || exit;

/*
 * functions that are directly available in WordPress themes (and plugins)
 */

/**
 * Return ad content
 *
 * @param int $id id of the ad (post)
 * @param array $args additional arguments
 * @return false|mixed|void
 */
function vk_adnetwork_get_ad($id = 0, $args = []){
    if ( defined( 'VK_ADNETWORK_DISABLE_CHANGE' ) && VK_ADNETWORK_DISABLE_CHANGE ) {
        $args = [];
    }

    return VK_Adnetwork_Select::get_instance()->get_ad_by_method( $id, 'id', $args );
}

/**
 * Echo an ad
 *
 * @since 1.0.0
 * @param int $id id of the ad (post)
 * @param array $args additional arguments
 */
function vk_adnetwork_the_ad($id = 0, $args = []){
    echo wp_kses(vk_adnetwork_get_ad( $id, $args ), wp_kses_allowed_html( 'post' ) + [ 'script' => true ] );
}

/**
 * Return content of an ad placement
 *
 * @since 1.1.0
 * @param string $id slug of the ad placement
 *
 */
function vk_adnetwork_get_ad_placement( $id = '', $args = [] ) {
    if ( defined( 'VK_ADNETWORK_DISABLE_CHANGE' ) && VK_ADNETWORK_DISABLE_CHANGE ) {
        $args = [];
    }
    return VK_Adnetwork_Select::get_instance()->get_ad_by_method( $id, 'placement', $args );
}

/**
 * Return content of an ad placement
 *
 * @since 1.1.0
 * @param string $id slug of the ad placement
 */
function vk_adnetwork_the_ad_placement($id = ''){
    echo wp_kses(vk_adnetwork_get_ad_placement( $id ), wp_kses_allowed_html( 'post' ) + [ 'script' => true ] );
}

/**
 * Return true if ads can be displayed
 *
 * @since 1.4.9
 * @return bool, true if ads can be displayed
 */
function vk_adnetwork_can_display_ads(){
    return VK_Adnetwork::get_instance()->can_display_ads();
}

/**
 * Are we currently on an AMP URL? * Находимся ли мы в данный момент по URL-адресу AMP?
 * Will always return `false` and show PHP Notice if called before the `wp` hook.
 * Всегда возвращает `false` и показывает уведомление PHP, если вызывается перед подключением `wp`.
 *
 * @return bool true if amp url, false otherwise
 */
function vk_adnetwork_is_amp() {
    global $pagenow;
    if ( is_admin()
        || is_embed()
        || is_feed()
        || ( isset( $pagenow ) && in_array( $pagenow, [ 'wp-login.php', 'wp-signup.php', 'wp-activate.php' ], true ) )
        || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
        || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
    ) {
        return false;
    }

    if ( ! did_action( 'wp' ) ) {
        return false;
    }


    return ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() )
    || ( function_exists( 'is_wp_amp' ) && is_wp_amp() )
    || ( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() )
    || ( function_exists( 'is_penci_amp' ) && is_penci_amp() )
    || isset( $_GET [ 'wpamp' ] );                              // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}


