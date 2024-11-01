<?php
defined('ABSPATH') || exit;

/**
 * Array with ad health messages
 *
 * Attribute: type
 * - "notice" (default, recommendation, etc.)
 * - "problem" (critical)
 *
 * attribute: can_hide
 * (user can see a button to hide this warning, default: true)
 *
 * attribute: hide
 * (how to handle click on "hide" button)
 * - true (default, hide the item)
 * - false (remove the item completely from list of notifications)
 *
 * attribute: timeout
 * (for how long to hide/ignore the message in seconds.)
 * - default: empty
 *
 * attribute: get_help_link
 * (enter URL, if exists, will add a link after the message)
 */
$vk_adnetwork_ad_health_notices = apply_filters(
    'vk-adnetwork-ad-health-notices',
    [
        // old PHP version
        // checked using VK_Adnetwork_Checks::php_version_minimum().
        'old_php'                                       => [
            'text' => sprintf(
            // translators: %1$s is a version number.
                wp_kses(__( 'Your <strong>PHP version (%1$s) is too low</strong>. VK AdNetwork is built for PHP %2$s and higher. It might work, but updating PHP is highly recommended. Please ask your hosting provider for more information.', 'vk-adnetwork' ), ['strong' => true]),
                phpversion(),
                VK_Adnetwork_Checks::MINIMUM_PHP_VERSION
            ),
            'type' => 'problem',
        ],
        // conflicting plugins found
        // VK_Adnetwork_Checks::conflicting_plugins().
        'conflicting_plugins'                           => [
            'text' => sprintf(
                // Плагины, которые известны тем, что вызывают (некоторые) проблемы:
                // translators: %1$s is a list of plugin names; %2$s a target URL.
                wp_kses(__( 'Plugins that are known to cause (partial) problems: <strong>%1$s</strong>. <a href="%2$s" target="_blank">Learn more</a>.', 'vk-adnetwork' ), ['strong' => true, 'a' => ['href' => true, 'target' => true]]),
                implode( ', ', VK_Adnetwork_Checks::conflicting_plugins() ),
                esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#plugins-that-are-known-to-cause-partial-problems' ) )
            ),
            'type' => 'problem',
        ],
        // PHP extensions missing
        // VK_Adnetwork_Checks::php_extensions().
        'php_extensions_missing'                        => [
            'text' => sprintf(
            // translators: %s is a list of PHP extensions.
                esc_html__( 'Missing PHP extensions could cause issues. Please ask your hosting provider to enable them: %s', 'vk-adnetwork' ),
                implode( ', ', VK_Adnetwork_Checks::php_extensions() )
            ),
            'type' => 'problem',
        ],
        // ads are disabled
        // VK_Adnetwork_Checks::ads_disabled().
        'ads_disabled'                                  => [
            'text' => sprintf(
            // translators: %s is a target URL.
                wp_kses(__( 'Ads are disabled for all or some pages. See "disabled ads" in <a href="%s">settings</a>.', 'vk-adnetwork' ), ['a' => ['href' => true]]),
                admin_url( 'admin.php?page=vk-adnetwork-settings#top#general' )
            ),
            'type' => 'problem',
        ],
        // check if VK AdNetwork related constants are enabled
        // VK_Adnetwork_Checks::get_defined_constants().
        'constants_enabled'                             => [
            'text' => '<a href="' . admin_url( 'admin.php?page=vk-adnetwork-support' ) . '">' . esc_html__( 'VK AdNetwork related constants enabled', 'vk-adnetwork' ) . '</a>',
            'type' => 'notice',
        ],


        // an individual ad expired.
        'ad_expired'                                    => [
            'text' => esc_html__( 'Ad expired', 'vk-adnetwork' ) . ': ',
            'type' => 'notice',
            'hide' => false,
        ],

    ]
);
//echo '<pre>'; print_r($vk_adnetwork_ad_health_notices); echo '</pre>';
