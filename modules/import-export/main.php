<?php
defined('ABSPATH') || exit;

class_exists( 'VK_Adnetwork', false ) || exit();

if ( is_admin() ) {
    add_action( 'vk-adnetwork-submenu-pages', 'vk_adnetwork_add_import_export_submenu', 10, 2 );
    VK_Adnetwork_Export::get_instance();

    /**
     * Add import & export page
     *
     * @param string $plugin_slug      The slug slug used to add a visible page.
     * @param string $hidden_page_slug The slug slug used to add a hidden page.
     */
    function vk_adnetwork_add_import_export_submenu( $plugin_slug, $hidden_page_slug = null ) {
        add_submenu_page(
            $hidden_page_slug,
            esc_html__( 'Import &amp; Export', 'vk-adnetwork' ),
            esc_html__( 'Import &amp; Export', 'vk-adnetwork' ),
            VK_Adnetwork_Plugin::user_cap( 'VK_Adnetwork_manage_options' ),
            $plugin_slug . '-import-export',
            'vk_adnetwork_display_import_export_page'
        );
    }

    /**
     * Render the import & export page
     *
     */
    function vk_adnetwork_display_import_export_page() {
        VK_Adnetwork_Import::get_instance()->dispatch();
        $messages = array_merge( VK_Adnetwork_Import::get_instance()->get_messages(), VK_Adnetwork_Export::get_instance()->get_messages() );

        include VK_ADNETWORK_BASE_PATH . 'modules/import-export/views/page.php';
    }
}

add_action( 'vk-adnetwork-cleanup-import-file', 'vk_adnetwork_delete_old_import_file' );

/**
 * Delete old import file via cron
 *
 */
function vk_adnetwork_delete_old_import_file( $path ) {
    //error_log( 'delete_old_xml_file ' . $path );
    if ( file_exists( $path ) ) {
        @wp_delete_file( $path );
    }
}



