<?php
/**
 * VK AdNetwork Abstract Ad Type
 *
 * @package   VK_Adnetwork
 * @license   GPL-2.0+
 * @link      https://vk.com
 * @copyright 2023 VK
 *
 * Class containing information that are defaults for all the other ad types
 *
 * see ad_type_content.php for an example on ad type
 *
 */
class VK_Adnetwork_Ad_Type_Abstract {

    /**
     * ID - internal type of the ad type
     *
     * must be static so set your own ad type ID here
     * use slug like format, only lower case, underscores and hyphens
     *
     * @since 1.0.0
     */
    public $ID = '';

    /**
     * Public title
     *
     * will be set within __construct so one can localize it
     *
     * @since 1.0.0
     */
    public $title = '';

    /**
     * Description of the ad type
     *
     * will be set within __construct so one can localize it
     *
     * @since 1.0.0
     */
    public $description = '';

    /**
     * Parameters of the ad
     *
     * defaults are set in construct
     */
    public $parameters = [];

    /**
     * Output for the ad parameters metabox
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     */
    public function render_parameters( VK_Adnetwork_Ad &$ad ) {
        /**
        * This will be loaded by default or using ajax when changing the ad type radio buttons
        * echo the output right away here
        * name parameters must be in the "vk_adnetwork" array
         */
    }

    /**
     * Render preview on the ad overview list
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     */
    public function render_preview( VK_Adnetwork_Ad $ad ) {}

    /**
     * Render additional information in the ad type tooltip on the ad overview page
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     */
    public function render_ad_type_tooltip( VK_Adnetwork_Ad $ad ) {}

    /**
     * Sanitize ad options on save
     *
     * @param array $options all ad options.
     * @return array sanitized ad options.
     * @since 1.0.0
     */
    public function sanitize_options( $options = [] ) {
        return $options;
    }

    /**
     * Sanitize content field on save
     *
     * @param string $content ad content
     * @return string $content sanitized ad content
     * @since 1.0.0
     */
    public function sanitize_content(string $content = ''): string
    {
        return $content = wp_unslash( $content );
    }

    /**
     * Load content field for the ad
     *
     * @param object $post WP post object
     * @return string $content ad content
     * @since 1.0.0
     */
    public function load_content(object $post): string
    {
        return $post->post_content;
    }

    /**
     * Prepare the ads frontend output
     *
     * @param VK_Adnetwork_Ad $ad The current ad object.
     *
     * @return string $content ad content prepared for frontend output
     * @since 1.0.0
     */
    public function prepare_output( $ad ) {
        return $ad->content;
    }

    /**
     * Process shortcodes.
     *
     * @param string          $output Ad content.
     * @param VK_Adnetwork_Ad $ad     The current ad object.
     *
     * @return string
     */
    protected function do_shortcode( $output, VK_Adnetwork_Ad $ad ) {
        $ad_options = $ad->options();

        if ( ! isset( $ad_options['output']['has_shortcode'] ) || $ad_options['output']['has_shortcode'] ) {
            // Store arguments so that shortcode hooks can access it.
            $ad_args = $ad->args;
            $ad_args['shortcode_ad_id'] = $ad->id;
            $output = preg_replace( '/\[(vk_adnetwork_the_ad_placement|vk_adnetwork_the_ad)/', '[$1 ad_args="' . urlencode( wp_json_encode( $ad_args ) )  . '"', $output );
        }

        return do_shortcode( $output );
    }
}
