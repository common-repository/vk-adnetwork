<?php
defined('ABSPATH') || exit;

/**
 * The view for the import & export page
 *
 * modules/import-export/classes/export.php > modules/import-export/main.php
 *   > vk_adnetwork_add_import_export_submenu > vk_adnetwork_display_import_export_page
 *
 * @var $messages
 */

class_exists( 'VK_Adnetwork', false ) || exit();

?><div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php
    foreach( $messages as $_message ) : ?>
        <div class="<?php echo esc_attr($_message[0] === 'error' ? 'error' : 'updated'); ?>"><p><?php echo esc_html($_message[1]); ?></p></div>
    <?php endforeach; ?>


    <h2><?php esc_html_e( 'Export', 'vk-adnetwork' ); ?></h2>
    <p><?php esc_html_e( 'When you click the button below VK AdNetwork will create an XML file for you to save to your computer.', 'vk-adnetwork' ); ?></p>

    <form method="post" action="">
        <fieldset>
            <input type="hidden" name="action" value="export" />
            <?php wp_nonce_field( 'vk_adnetwork-export' ); ?>
            <p><label><input type="checkbox" name="content[]" value="ads" checked="checked" /> <?php esc_html_e( 'Ads', 'vk-adnetwork' ); ?></label></p>
            <p><label><input type="checkbox" name="content[]" value="placements" checked="checked" /> <?php esc_html_e( 'Placements', 'vk-adnetwork' ); ?></label></p>
            <p><label><input type="checkbox" name="content[]" value="options" /> <?php esc_html_e( 'Options', 'vk-adnetwork' ); ?></label></p>
        </fieldset>
        <?php submit_button( esc_html__( 'Download Export File', 'vk-adnetwork' ) ); ?>
    </form>



    <h2><?php esc_html_e( 'Import', 'vk-adnetwork' ); ?></h2>
    <?php
    // filter the maximum allowed upload size for import files
    $bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
    $size = size_format( $bytes );
    $upload_dir = wp_upload_dir();
    ?>

    <form enctype="multipart/form-data" id="import-upload-form" method="post" action="">
        <?php wp_nonce_field( 'vk_adnetwork-import' ); ?>
        <fieldset>
            <p><label><input class="vk_adnetwork_import_type" type="radio" name="import_type" value="xml_file" checked="checked" /> <?php esc_html_e( 'Choose an XML file', 'vk-adnetwork' ); ?></label></p>
            <p><label><input class="vk_adnetwork_import_type" type="radio" name="import_type" value="xml_content" disabled="disabled"/> <?php esc_html_e( 'Copy an XML content', 'vk-adnetwork' ); ?></label></p>
        </fieldset>

        <div id="vk_adnetwork_xml_file">
            <?php
            if ( ! empty( $upload_dir['error'] ) ) : ?>
                <p class="vk_adnetwork-notice-inline vk_adnetwork-error">
                    <?php esc_html_e( 'Before you can upload your import file, you will need to fix the following error:', 'vk-adnetwork' ); ?>
                    <strong><?php echo esc_html($upload_dir['error']); ?>guu</strong>
                </p>
            <?php else: ?>
                <p>
                    <input type="file" id="upload" name="import" size="25" /> (<?php
                        // Максимальный размер:
                        // translators: %s is a number -- the maximum file size
                        printf( esc_html__( 'Maximum size: %s', 'vk-adnetwork' ), esc_html($size) );
                    ?>)
                    <input type="hidden" name="max_file_size" value="<?php echo absint($bytes); ?>" />
                </p>
            <?php endif; ?>
        </div>
        <div id="vk_adnetwork_xml_content" style="display:none;">
            <p><textarea id="xml_textarea" name="xml_textarea" rows="10" cols="20" class="large-text code"></textarea></p>
        </div>
        <?php submit_button( esc_html__( 'Start import', 'vk-adnetwork' ), 'primary' ); ?>
    </form>

</div>






