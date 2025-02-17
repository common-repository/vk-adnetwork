<?php

// autoload_static.php @generated by Composer

namespace VK_Adnetwork\Composer\Autoload;

use Closure;

class vk_adnetwork_ComposerStaticInit
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'VK_Adnetwork\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'VK_Adnetwork\\' =>
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'D' => 
        array (
            'Detection' => 
            array (
                0 => __DIR__ . '/..' . '/mobiledetect/mobiledetectlib/namespaced',
            ),
        ),
    );

    public static $classMap = array (
        'VK_AdnetworkAd' => __DIR__ . '/../..' . '/classes/ad.php',
        'VK_Adnetwork' => __DIR__ . '/../..' . '/public/class-vk-adnetwork.php',
        'VK_Adnetwork\\Placement_Type' => __DIR__ . '/../..' . '/src/Placement_Type.php',
        'VK_Adnetwork\\Placement_Type_Options' => __DIR__ . '/../..' . '/src/Placement_Type_Options.php',
        'VK_Adnetwork_Ad' => __DIR__ . '/../..' . '/classes/ad.php',
        'VK_Adnetwork_Ad_Ajax_Callbacks' => __DIR__ . '/../..' . '/classes/ad_ajax_callbacks.php',
        'VK_Adnetwork_Ad_Debug' => __DIR__ . '/../..' . '/classes/ad-debug.php',
        'VK_Adnetwork_Ad_Expiration' => __DIR__ . '/../..' . '/classes/ad-expiration.php',
        'VK_Adnetwork_Ad_Health_Notices' => __DIR__ . '/../..' . '/classes/ad-health-notices.php',
        'VK_Adnetwork_Ad_List_Filters' => __DIR__ . '/../..' . '/admin/includes/class-list-filters.php',
        'VK_Adnetwork_Ad_Type_Abstract' => __DIR__ . '/../..' . '/classes/ad_type_abstract.php',
        'VK_Adnetwork_Ad_Type_Plain' => __DIR__ . '/../..' . '/classes/ad_type_plain.php',
        'VK_Adnetwork_Admin' => __DIR__ . '/../..' . '/admin/class-vk-adnetwork-admin.php',
        'VK_Adnetwork_Admin_Ad_Type' => __DIR__ . '/../..' . '/admin/includes/class-ad-type.php',
        'VK_Adnetwork_Admin_Menu' => __DIR__ . '/../..' . '/admin/includes/class-menu.php',
        'VK_Adnetwork_Admin_Meta_Boxes' => __DIR__ . '/../..' . '/admin/includes/class-meta-box.php',
        'VK_Adnetwork_Admin_Notices' => __DIR__ . '/../..' . '/admin/includes/class-notices.php',
        'VK_Adnetwork_Admin_Settings' => __DIR__ . '/../..' . '/admin/includes/class-settings.php',
        'VK_Adnetwork_Admin_Upgrades' => __DIR__ . '/../..' . '/admin/includes/class-admin-upgrades.php',
        'VK_Adnetwork_Ajax' => __DIR__ . '/../..' . '/classes/ad-ajax.php',
        'VK_Adnetwork_Checks' => __DIR__ . '/../..' . '/classes/checks.php',
        'VK_Adnetwork_Compatibility' => __DIR__ . '/../..' . '/classes/compatibility.php',
        'VK_Adnetwork_Filesystem' => __DIR__ . '/../..' . '/classes/filesystem.php',
        'VK_Adnetwork_Frontend_Checks' => __DIR__ . '/../..' . '/classes/frontend_checks.php',
        'VK_Adnetwork_Frontend_Notices' => __DIR__ . '/../..' . '/classes/frontend-notices.php',
        'VK_Adnetwork_In_Content_Injector' => __DIR__ . '/../..' . '/classes/in-content-injector.php',
        'VK_Adnetwork_Inline_Css' => __DIR__ . '/../..' . '/classes/inline-css.php',
        'VK_Adnetwork_Modal' => __DIR__ . '/../..' . '/classes/VK_Adnetwork_Modal.php',
        'VK_Adnetwork_Model' => __DIR__ . '/../..' . '/classes/ad-model.php',
        'VK_Adnetwork_Overview_Widgets_Callbacks' => __DIR__ . '/../..' . '/admin/includes/class-overview-widgets.php',
        'VK_Adnetwork_Placements' => __DIR__ . '/../..' . '/classes/ad_placements.php',
        'VK_Adnetwork_Plugin' => __DIR__ . '/../..' . '/classes/plugin.php',
        'VK_Adnetwork_Select' => __DIR__ . '/../..' . '/classes/ad-select.php',
        'VK_Adnetwork_Upgrades' => __DIR__ . '/../..' . '/classes/upgrades.php',
        'VK_Adnetwork_Utils' => __DIR__ . '/../..' . '/classes/utils.php',

        'VK_Adnetwork_Widget' => __DIR__ . '/../..' . '/classes/widget.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = vk_adnetwork_ComposerStaticInit::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = vk_adnetwork_ComposerStaticInit::$prefixDirsPsr4;
            $loader->prefixesPsr0 = vk_adnetwork_ComposerStaticInit::$prefixesPsr0;
            $loader->classMap = vk_adnetwork_ComposerStaticInit::$classMap;

        }, null, ClassLoader::class);
    }
}
