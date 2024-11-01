<?php
class VK_Adnetwork_Ad_Debug {
    /**
     * Prepare debug mode output.
     *
     * @param VK_Adnetwork_Ad $ad
     * @return mixed|void
     */
    public function prepare_debug_output( VK_Adnetwork_Ad $ad ) {
        global $post, $wp_query;

        // set size
        if ( $ad->width > 100 && $ad->height > 100 ){
            $width = $ad->width;
            $height = $ad->height;
        } else {
            $width = 300;
            $height = 250;
        }

        $style = "width:{$width}px;height:{$height}px;background-color:#ddd;overflow:scroll;";
        $style_full = 'width: 100%; height: 100vh; background-color: #ddd; overflow: scroll; position: fixed; top: 0; left: 0; min-width: 600px; z-index: 99999;';

        if ( ! empty( $ad->wrapper['id']) ) {
            $wrapper_id = $ad->wrapper['id'];
        } else {
            $wrapper_id = VK_Adnetwork_Plugin::get_instance()->get_frontend_prefix() . wp_rand();
        }

        $content = [];

        if ( $ad->can_display( [ 'ignore_debugmode' => true ] ) ) {
            $content[] = esc_html__( 'The ad is displayed on the page', 'vk-adnetwork' );
        } else {
            $content[] = esc_html__( 'The ad is not displayed on the page', 'vk-adnetwork' );
        }

        // compare current wp_query with global wp_main_query
        if ( ! $wp_query->is_main_query() ) {
            $content[] = wp_kses( sprintf( '<span style="color: red;">%s</span>', esc_html__( 'Current query is not identical to main query.', 'vk-adnetwork' ) ), ['span' => ['style' => true]]);
            // output differences
            $content[] = $this->build_query_diff_table();
        }

        if ( isset( $post->post_title ) && isset( $post->ID ) ) {
            $content[] = esc_html(sprintf( '%s: %s, %s: %s', esc_html__( 'current post', 'vk-adnetwork' ), $post->post_title, 'ID', $post->ID ));
        }
        // compare current post with global post
        if ( $wp_query->post !== $post ){
            $error = wp_kses( sprintf( '<span style="color: red;">%s</span>', esc_html__( 'Current post is not identical to main post.', 'vk-adnetwork' ) ), ['span' => ['style' => true]]);
            if ( isset( $wp_query->post->post_title ) && $wp_query->post->ID ) {
                $error .= wp_kses( sprintf( '<br />%s: %s, %s: %s', esc_html__( 'main post', 'vk-adnetwork' ), $wp_query->post->post_title, 'ID', $wp_query->post->ID ), ['br' => true]);
            }
            $content[] = $error;
        }

        $content[] = $this->build_call_chain( $ad );

        if ( $message = self::is_https_and_http( $ad ) ) {
            $content[] = wp_kses( sprintf( '<span style="color: red;">%s</span>', esc_html($message) ), ['span' => ['style' => true]]);
        }

        $content = apply_filters( 'vk-adnetwork-ad-output-debug-content', $content, $ad );

        ob_start();

        include( VK_ADNETWORK_BASE_PATH . 'public/views/ad-debug.php' );

        $output = ob_get_clean();

        // apply a custom filter by ad type
        $output = apply_filters( 'vk-adnetwork-ad-output-debug', $output, $ad );
        $output = apply_filters( 'vk-adnetwork-ad-output', $output, $ad );

        return $output;

    }

    /**
     * Build table with differences between current and main query
     *
     * @since 1.7.0.3
     */
    protected function build_query_diff_table(){

        global $wp_query, $wp_the_query;

        $diff_current = array_diff_assoc( $wp_query->query_vars, $wp_the_query->query_vars );
        $diff_main = array_diff_assoc( $wp_the_query->query_vars, $wp_query->query_vars );

        if( ! is_array( $diff_current ) || ! is_array( $diff_main ) ){
            return '';
        }

        ob_start();

        ?><table>
        <thead><tr>
            <th></th>
            <th><?php esc_html_e( 'current query', 'vk-adnetwork'); ?></th>
            <th><?php esc_html_e( 'main query', 'vk-adnetwork'); ?></th>
        </tr></thead><?php
        foreach( $diff_current as $_key => $_value ){
            ?><tr>
                <td><?php echo esc_html($_key); ?></td>
                <td><?php echo esc_html($_value); ?></td>
                <td><?php echo esc_html($diff_main[$_key] ?? ''); ?></td>
            </tr><?php
        }
        ?></table><?php

        return ob_get_clean();
    }

    /**
     * Build call chain (placement->group->ad)
     *
     * @param VK_Adnetwork_Ad $ad
     * @return string
     */
    protected function build_call_chain( VK_Adnetwork_Ad $ad ) {
        ob_start();

        $options = $ad->options();

        printf( esc_html('%s: %s (%s)'), esc_html__( 'Ad', 'vk-adnetwork' ), esc_html( $ad->title ), absint($ad->id) );

        if ( isset( $options['output']['placement_id'] ) ) {
            $placements = VK_Adnetwork::get_ad_placements_array();
            $placement_id = $options['output']['placement_id'];
            $placement_name = $placements[$placement_id]['name'] ?? '';
            printf( wp_kses('<br />%s: %s (%s)', ['br' => true]), esc_html__( 'Placement', 'vk-adnetwork' ), esc_html( $placement_name ), esc_html( $placement_id ) );
        }

        return ob_get_clean();
    }

    /**
     * Check if the current URL is HTTPS, but the ad code contains HTTP.
     *
     * @param VK_Adnetwork_Ad $ad
     * @return bool false/string
     */
    public static function is_https_and_http( VK_Adnetwork_Ad $ad ) {
        if ( is_ssl()
            && ( $ad->type === 'plain' || $ad->type === 'content' )
            // Find img, iframe, script. '\\\\' denotes a single backslash
            && preg_match( '#\ssrc=\\\\?[\'"]http:\\\\?/\\\\?/#i', $ad->content )
        ) {
            return esc_html__( 'Your website is using HTTPS, but the ad code contains HTTP and might not work.', 'vk-adnetwork' );
        }

        return false;
    }
}
