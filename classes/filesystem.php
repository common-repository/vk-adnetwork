<?php
/**
 * @since 1.7.17
 */
class VK_Adnetwork_Filesystem {
    /**
     * Singleton instance of the class
     *
     * @var VK_Adnetwork_Filesystem
     */
    protected static $instance;

    /**
     * Return an instance of VK_Adnetwork_Filesystem
     *
     * @return  VK_Adnetwork_Filesystem
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {}

    /**
     * Print the filesystem credentials modal when needed.
     */
    public function print_request_filesystem_credentials_modal() {
        $filesystem_method = get_filesystem_method();
        ob_start();
        $filesystem_credentials_are_stored = request_filesystem_credentials( self_admin_url() );
        ob_end_clean();
        $request_filesystem_credentials = ( $filesystem_method != 'direct' && ! $filesystem_credentials_are_stored );
        if ( ! $request_filesystem_credentials ) {
            return;
        }
        ?>
        <div id="vk-adnetwork-rfc-dialog" class="notification-dialog-wrap request-filesystem-credentials-dialog">
            <div class="notification-dialog-background"></div>
            <div class="notification-dialog" role="dialog" aria-labelledby="request-filesystem-credentials-title" tabindex="0">
                <div class="request-filesystem-credentials-dialog-content">
                    <?php request_filesystem_credentials( site_url() ); ?>
                </div>
            </div>
        </div>
        <?php
    }
}
