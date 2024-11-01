<?php

/**
 * Abstracts ad selection.
 *
 * The class allows to modify 'methods' (named callbacks) to provide ads
 * through `vk-adnetwork-ad-select-methods` filter.
 * This can be used to replace default methods, wrap them or add new ones.
 *
 * Further allows to provide ad selection attributes
 * through `vk-adnetwork-ad-select-args` filter to influence behaviour of the
 * selection method.
 * Default methods have a `override` attribute that allows to replace the
 * content. This may be used to defer or skip ad codes dynamically.
 *
 * @since 1.5.0
 */
class VK_Adnetwork_Select {

    const PLACEMENT = 'placement';
    const AD = 'id'; // alias of self::ID
    const ID = 'id';

    protected $methods;

    private function __construct() {}

    /**
     *
     * @var VK_Adnetwork_Select
     */
    private static $instance;

    /**
     *
     * @return VK_Adnetwork_Select
     */
    public static function get_instance()
    {
        if ( ! isset(self::$instance) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     *
     * @return array
     */
    public function get_methods()
    {
        if ( ! isset($this->methods) ) {
            $methods = [
                self::AD => [ $this, 'get_ad_by_id' ],
                self::PLACEMENT => [ $this, 'get_ad_by_placement' ],
            ];

            $this->methods = apply_filters( 'vk-adnetwork-ad-select-methods', $methods );
        }

        return $this->methods;
    }

    /**
     * Advanced ad selection methods should not directly rely on
     * current environment factors.
     * Prior to actual ad selection the meta is provided to allow for
     * serialised, proxied or otherwise defered selection workflows.
     *
     * @return array
     */
    public function get_ad_arguments( $method, $id, $args = [] )
    {
        $args = (array) $args;

        $args['previous_method'] = $args['method'] ?? null;
        $args['previous_id'] = $args['id'] ?? null;

        if ( $id || ! isset( $args['id'] ) ) $args['id'] = $id;
        $args['method'] = $method;

        $args = apply_filters( 'vk-adnetwork-ad-select-args', $args, $method, $id );

        return $args;
    }

    /**
     * @param $id
     * @param $method
     * @param $args
     * @return false|mixed|void
     */
    public function get_ad_by_method( $id, $method, $args = [] ) {

        $methods = $this->get_methods();
        if ( ! isset($methods[ $method ]) ) {
            return ;
        }
        if ( ! vk_adnetwork_can_display_ads() ) {
            return ;
        }
        $args = $this->get_ad_arguments( $method, $id, $args );

        return call_user_func( $methods[ $method ], $args );
    }

    // internal
    public function get_ad_by_id($args) {
        if ( isset($args['override']) ) {
            return $args['override'];
        }
        if ( ! isset($args['id']) || $args['id'] == 0 ) {
            return ;
        }

        // get ad
        $ad = new VK_Adnetwork_Ad( (int) $args['id'], $args );

        if ( false !== ( $override = apply_filters( 'vk-adnetwork-ad-select-override-by-ad', false, $ad, $args ) ) ) {
            return $override;
        }

        // check conditions
        if ( $ad->can_display() ) {
            return $ad->output();
        }
    }

    // internal
    public function get_ad_by_placement($args) {
        if ( isset($args['override']) ) {
            return $args['override'];
        }
        if ( ! isset($args['id']) || $args['id'] == '' ) {
            return ;
        }

        // check conditions
        if ( ! VK_Adnetwork_Placements::can_display( $args['id'] ) ) {
            return;
        }

        // get placement content
        $id = $args['id'];
        return VK_Adnetwork_Placements::output( $id, $args );
    }
}
