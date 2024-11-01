<?php

/**
 * VK AdNetwork Model
 */
class VK_Adnetwork_Model {

    /**
     * Default time-to-live for WP Object Cache
     *
     * @var string
     */
    const OBJECT_CACHE_TTL = 720; // 12 Minutes

    /**
     * WordPress database object.
     *
     * @var wpdb
     */
    protected $db;

    /**
     * Placements
     *
     * @var array
     */
    protected $ad_placements;

    /**
     * VK_Adnetwork_Model constructor.
     *
     * @param wpdb $wpdb WordPress database access.
     */
    public function __construct( wpdb $wpdb ) {
        $this->db = $wpdb;
    }

    /**
     * Load all ads based on WP_Query conditions
     * Загрузить все объявления в соответствии с условиями WP_Query
     *
     * @since 1.1.0
     * @param array $args WP_Query arguments that are more specific that default.
     * содержит аргументы WP_Query, которые являются более конкретными, чем по умолчанию.
     * @return array $ads array with post objects.
     * массив объявлений с объектами post
     */
    public function get_ads( $args = [] ) {
        $args = wp_parse_args( $args, [
            'posts_per_page' => -1,
            'post_status'    => [ 'publish', 'future' ],
        ] );
        // add default WP_Query arguments.
        $args['post_type'] = VK_Adnetwork::POST_TYPE_SLUG;

        return ( new WP_Query( $args ) )->posts;
    }

    /**
     * Get the array with ad placements
     *
     * @since 1.1.0
     * @return array $ad_placements
     */
    public function get_ad_placements_array() {

        if ( ! isset( $this->ad_placements ) ) {
            $this->ad_placements = get_option( 'vk_adnetwork-ads-placements', [] );

            // load default array if not saved yet.
            if ( ! is_array( $this->ad_placements ) ) {
                $this->ad_placements = [];
            }

            $this->ad_placements = apply_filters( 'vk-adnetwork-get-ad-placements-array', $this->ad_placements );
        }
        // echo '<! -- /classes/ad-model.php:get_ad_placements_array ~ '; print_r($this->ad_placements); echo '-->';
        return $this->ad_placements;
    }

    /**
     * Reset placement array.
     */
    public function reset_placement_array() {
        $this->ad_placements = null;
    }

    /**
     * Update the array with ad placements
     *
     * @param array $ad_placements array with placements.
     */
    public function update_ad_placements_array( $ad_placements ) {
        $ad_placements = VK_Adnetwork_Placements::sort( $ad_placements, 'type' );
        update_option( 'vk_adnetwork-ads-placements', $ad_placements );
        $this->ad_placements = $ad_placements;
    }



}
