<?php
/**
 * VK AdNetwork.
 *
 * @package   VK_Adnetwork
 * @author    VK Ad Network <adnetwork_support@vk.company>
 * @license   GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       VK AdNetwork
 * Plugin URI:        https://ads.vk.com/
 * Description:       Manage VK AdNetwork ads in WordPress
 * Version:           4.06
 * Author:            VK Ad Network
 * Text Domain:       vk-adnetwork
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// only load if not already existing (maybe included from another plugin).
if ( defined( 'VK_ADNETWORK_BASE_PATH' ) ) {
    return;
}

// load basic path to the plugin.
define( 'VK_ADNETWORK_BASE', plugin_basename( __FILE__ ) ); // plugin base as used by WordPress to identify it.
define( 'VK_ADNETWORK_BASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'VK_ADNETWORK_BASE_URL', plugin_dir_url( __FILE__ ) );
define( 'VK_ADNETWORK_BASE_DIR', dirname( VK_ADNETWORK_BASE ) ); // directory of the plugin without any paths.
// general and global slug, e.g. to store options in WP.
const VK_ADNETWORK_SLUG = 'vk-adnetwork';
const VK_ADNETWORK_URL = 'https://ads.vk.com/'; // -x- 'https://target.my.com/';
const VK_ADNETWORK_VERSION = '4.06';

// Autoloading, modules and functions.

// load public functions (might be used by modules, other plugins or theme).
require_once VK_ADNETWORK_BASE_PATH . 'includes/functions.php';
require_once VK_ADNETWORK_BASE_PATH . 'includes/load_modules.php';
require_once VK_ADNETWORK_BASE_PATH . 'includes/cap_map.php';

VK_Adnetwork_ModuleLoader::getLoader(); // enable autoloading.

// Public-Facing and Core Functionality.

VK_Adnetwork::get_instance();
VK_Adnetwork_ModuleLoader::loadModules( VK_ADNETWORK_BASE_PATH . 'modules/' ); // enable modules, requires base class.

// Dashboard and Administrative Functionality.

if ( is_admin() ) {
    VK_Adnetwork_Admin::get_instance();
}
