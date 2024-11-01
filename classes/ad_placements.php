<?php

/**
 * VK AdNetwork
 *
 * @package   VK_Adnetwork_Placements
 * @license   GPL-2.0+
 * @link      https://vk.com
 * @copyright 2023 VK
 */

use VK_Adnetwork\Placement_Type;

/**
 * Grouping placements functions
 *
 * @since 1.1.0
 * @package VK_Adnetwork_Placements
 */
class VK_Adnetwork_Placements {

    /**
     * Gather placeholders which later are replaced by the ads
     *
     * @var array $ads_for_placeholders
     */
    private static $ads_for_placeholders = [];
    /**
     * Temporarily change content during processing
     *
     * @var array $placements
     */
    private static $replacements = [
        'gcse:search' => 'gcse__search', // Google custom search namespaced tags.
    ];


    /**
     * Get placement types
     *
     * @return \VK_Adnetwork\Placement_Type[] $types array with placement types
     * @since 1.2.1
     */
    public static function get_placement_types() {
        $types = [

            'post_top'       => [
                'title'       => esc_html__( 'Before Content', 'vk-adnetwork' ),                            // Сверху контента
                'description' => esc_html__( 'Injected before the post content.', 'vk-adnetwork' ),         // Вставлено до содержимого записи.
                'image'       => VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/content-before.png',
                'order'       => 20,
                'options'     => [
                    'show_position'    => true,
                    'show_lazy_load'   => true,
                    'uses_the_content' => true,
                    'amp'              => true,
                ],
            ],

            'post_content'   => [
                'title'       => esc_html__( 'Content', 'vk-adnetwork' ),                                   // Содержание // Вставлено в содержимое. Вы можете выбрать параграф, после которого содержимое объявления будет показано.
                'description' => esc_html__( 'Injected into the content. You can choose the paragraph after which the ad content is displayed.', 'vk-adnetwork' ),
                'image'       => VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/content-within.png',
                'order'       => 21,
                'options'     => [
                    'show_position'    => true,
                    'show_lazy_load'   => true,
                    'uses_the_content' => true,
                    'amp'              => true,
                ],
            ],

            'post_bottom'    => [
                'title'       => esc_html__( 'After Content', 'vk-adnetwork' ),                             // После контента
                'description' => esc_html__( 'Injected after the post content.', 'vk-adnetwork' ),          // Вставлено после содержимого записи.
                'image'       => VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/content-after.png',
                'order'       => 35,
                'options'     => [
                    'show_position'    => true,
                    'show_lazy_load'   => true,
                    'uses_the_content' => true,
                    'amp'              => true,
                ],
            ],

            'default'        => [
                'title'       => esc_html__( 'Manual Placement', 'vk-adnetwork' ),                                  // Ручное размещение
                'description' => esc_html__( 'Manual placement to use as function or shortcode.', 'vk-adnetwork' ), // Ручное размещение для использования функции или шорткода.
                'image'       => VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/manual.png',
                'order'       => 80,
                'options'     => [
                    'show_position'  => true,
                    'show_lazy_load' => true,
                    'amp'            => true,
                ],
            ],

            'footer'         => [
                'title'       => esc_html__( 'Footer Code', 'vk-adnetwork' ),                                       // код футера // Вставлено в футер (перед закрывающим тегом &lt;/body&gt;).
                'description' => esc_html__( 'Injected in Footer (before closing &lt;/body&gt; Tag).', 'vk-adnetwork' ),
                'image'       => VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/footer.png',
                'order'       => 95,
                'options'     => [ 'amp' => true ],
            ],

            'sidebar_widget' => [
                'title'       => esc_html__( 'Sidebar Widget', 'vk-adnetwork' ),
                // Создайте виджет боковой панели с рекламой. Может быть размещен и использован как любой другой виджет.
                'description' => esc_html__( 'Create a sidebar widget with an ad. Can be placed and used like any other widget.', 'vk-adnetwork' ),
                'image'       => VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/widget.png',
                'order'       => 50,
                'options'     => [
                    'show_position'  => true,
                    'show_lazy_load' => true,
                    'amp'            => true,
                ],
            ],
        ];

        $types = (array) apply_filters( 'vk-adnetwork-placement-types', $types );

        foreach ( $types as $type => $definition ) {
            $types[ $type ] = new Placement_Type( $type, $definition );
        }

        return $types;
    }

    /**
     * Save a new placement
     *
     * @param array $new_placement information about the new placement.
     *
     * @return mixed slug if saved; false if not
     * @since 1.1.0
     */
    public static function save_new_placement( $new_placement ) {
        // load placements // -TODO use model.
        $placements = VK_Adnetwork::get_ad_placements_array();

        // важная новая фича! :: при каждой смене местоположения мы --
        // [1] создаём новый плайсмент
        // [2] удаляем все (старые/предыдущие) плайсменты с этим объявлением (обычно одно предыдущее)
        // в итоге у нас всегда 1:1 -- объявления:местоположения
        // т.е. объявление просто ПЕРЕМЕЩАЕТСЯ на новое место (move, а не copy)
        foreach ($placements as $slug => $placement) {
            if ($placement['item'] === $new_placement['item']) unset($placements[$slug]);
        }

        // create slug.
        $new_placement['slug'] = sanitize_title( $new_placement['name'] );

        if ( isset( $placements[ $new_placement['slug'] ] ) ) {
            $i = 1;
            // try to save placement until we found an empty slug.
            do {
                $i ++;
                if ( 100 === $i ) { // prevent endless loop, just in case.
                    VK_Adnetwork::log( 'endless loop when injecting placement' );
                    break;
                }
            } while ( isset( $placements[ $new_placement['slug'] . '_' . $i ] ) );

            $new_placement['slug'] .= '_' . $i;
            $new_placement['name'] .= ' ' . $i;
        }

        // check if slug already exists or is empty.
        if ( '' === $new_placement['slug'] || isset( $placements[ $new_placement['slug'] ] ) || ! isset( $new_placement['type'] ) ) {
            return false;
        }

        // make sure only allowed types are being saved.
        $placement_types       = self::get_placement_types();
        $new_placement['type'] = ( isset( $placement_types[ $new_placement['type'] ] ) ) ? $new_placement['type'] : 'default';
        // escape name.
        $new_placement['name'] = esc_attr( $new_placement['name'] );

        // add new place to all placements.
        $placements[ $new_placement['slug'] ] = [
            'type' => $new_placement['type'],
            'name' => $new_placement['name'],
            'item' => $new_placement['item'],
        ];

        // add index options.
        if ( isset( $new_placement['options'] ) ) {
            $placements[ $new_placement['slug'] ]['options'] = $new_placement['options'];
            if ( isset( $placements[ $new_placement['slug'] ]['options']['index'] ) ) {
                $placements[ $new_placement['slug'] ]['options']['index'] = absint( $placements[ $new_placement['slug'] ]['options']['index'] );
            }
        }

        // save array.
        VK_Adnetwork::get_instance()->get_model()->update_ad_placements_array( $placements );

        return $new_placement['slug'];
    }

    /**
     * Return content of a placement
     *
     * @param string $id slug of the display.
     * @param array  $args optional arguments (passed to child).
     *
     * @return false|mixed|void
     */
    public static function output( $id = '', $args = [] ) {
        // get placement data for the slug.
        if ( '' == $id ) {
            return;
        }

        $placements = VK_Adnetwork::get_ad_placements_array();
        $placement  = ( isset( $placements[ $id ] ) && is_array( $placements[ $id ] ) ) ? $placements[ $id ] : [];

        if ( isset( $args['change-placement'] ) ) {
            // some options was provided by the user.
            $placement = VK_Adnetwork_Utils::vk_adnetwork_merge_deep_array( [ $placement, $args['change-placement'] ] );
        }

        if ( isset( $placement['item'] ) && '' !== $placement['item'] ) {
            $_item = explode( '_', $placement['item'] );

            if (empty( $_item[1] )) {
                return;
            }

            // inject options.
            if ( isset( $placement['options'] ) && is_array( $placement['options'] ) ) {
                foreach ( $placement['options'] as $_k => $_v ) {
                    if ( ! isset( $args[ $_k ] ) ) {
                        $args[ $_k ] = $_v;
                    }
                }
            }

            // inject placement type.
            if ( isset( $placement['type'] ) ) {
                $args['placement_type'] = $placement['type'];
            }

            // options.
            $prefix = VK_Adnetwork_Plugin::get_instance()->get_frontend_prefix();

            // return either ad or group content.
            switch ( $_item[0] ) {
                case 'ad':
                case VK_Adnetwork_Select::AD:
                    // create class from placement id (not if header injection).
                    if ( ! isset( $placement['type'] ) || 'header' !== $placement['type'] ) {
                        if ( ! isset( $args['output'] ) ) {
                            $args['output'] = [];
                        }
                        if ( ! isset( $args['output']['class'] ) ) {
                            $args['output']['class'] = [];
                        }
                        $class = $prefix . $id;
                        if ( ! in_array( $class, $args['output']['class'] ) ) {
                            $args['output']['class'][] = $class;
                        }
                    }

                    // fix method id.
                    $_item[0] = VK_Adnetwork_Select::AD;
                    break;

                case VK_Adnetwork_Select::PLACEMENT:
                    // avoid loops (programmatical error).
                    return;

                default:
            }

            // create placement id for various features.
            $args['output']['placement_id'] = $id;

            // add the placement to the global output array.
            $vk_adnetwork = VK_Adnetwork::get_instance();
            $name   = $placement['name'] ?? $id;

            $result = VK_Adnetwork_Select::get_instance()->get_ad_by_method( (int) $_item[1], $_item[0], $args );

            if ( $result && ( ! isset( $args['global_output'] ) || $args['global_output'] ) ) {
                $vk_adnetwork->current_ads[] = [
                    'type'  => 'placement',
                    'id'    => $id,
                    'title' => $name,
                ];
            }

            return $result;
        }

    }

    /**
     * Inject ads directly into the content
     *
     * @param string $placement_id Id of the placement.
     * @param array  $placement_opts Placement options.
     * @param string $content Content to inject placement into.
     *
     * @return string $content Content with injected placement.
     * @since 1.2.1
     */
    public static function &inject_in_content( $placement_id, $placement_opts, &$content ) {
        return VK_Adnetwork_In_Content_Injector::inject_in_content( $placement_id, $placement_opts, $content );
    }

    /**
     * Check if the placement can be displayed
     *
     * @param int $id placement id.
     *
     * @return bool true if placement can be displayed
     * @since 1.6.9
     */
    public static function can_display( $id = 0 ) {
        if ( ! isset( $id ) || 0 === $id ) {
            return true;
        }

        return apply_filters( 'vk-adnetwork-can-display-placement', true, $id );
    }

    /**
     * Get the available items for the selected placement.
     *
     * @param string $type The current placement type.
     * @param string $item The ad/group id.
     *
     * @return array[]
     */
    public static function get_items_for_placement( string $type, string $item = 'ad_0' ) : iterable {
        $placement_type = self::get_placement_types()[ $type ];
        $items          = [
            'ads'    => [
                'label' => esc_html__( 'Ads', 'vk-adnetwork' ),
                'items' => $placement_type->get_allowed_ads(),
            ],
        ];

        return array_map( static function( $items_group ) use ( $item ) {
            array_walk( $items_group['items'], static function( &$value, $key ) use ( $item ) {
                $value = [
                    'name'     => $value,
                    'selected' => $key === $item,
                    'disabled' => false,
                ];
            } );

            return $items_group;
        }, $items );
    }

    /**
     * Sort placements
     *
     * @param array  $placements Existing placements.
     * @param string $orderby The field to order by. Accept `name` or `type`.
     * @return array $placements Sorted placements.
     */
    public static function sort( $placements, $orderby = 'name' ) {
        if ( ! is_array( $placements ) ) {
            return [];
        }
        if ( 'name' === $orderby ) {
            ksort( $placements, SORT_NATURAL );
            return $placements;
        }
        uasort( $placements, [ 'VK_Adnetwork_Placements', 'sort_by_type_callback' ] );
        return $placements;

    }

    /**
     * Callback to sort placements by type.
     *
     * @param array $f First placement.
     * @param array $s Second placement.
     * @return int 0 If placements are equal, -1 if the first should come first, 1 otherwise.
     */
    private static function sort_by_type_callback( $f, $s ) {
        // A placement with the "Words Between Ads" option set to non-zero gets injected after others
        // because it reads existing ads.
        if ( ! empty( $f['options']['words_between_repeats'] ) xor ! empty( $s['options']['words_between_repeats'] ) ) {
            return ! empty( $f['options']['words_between_repeats'] ) ? 1 : -1;
        }

        $types = self::get_placement_types();

        $f_o = ( isset( $f['type'] ) && isset( $types[ $f['type'] ]['order'] ) ) ? $types[ $f['type'] ]['order'] : 100;
        $s_o = ( isset( $s['type'] ) && isset( $types[ $s['type'] ]['order'] ) ) ? $types[ $s['type'] ]['order'] : 100;

        if ( $f_o === $s_o ) {
            // Sort by index.
            if ( 'post_content' === $f['type'] && isset( $f['options']['index'] ) && isset( $s['options']['index'] )
                && $f['options']['index'] !== $s['options']['index'] ) {
                return ( $f['options']['index'] < $s['options']['index'] ) ? -1 : 1;
            }

            // Sort by name.
            if ( isset( $f['name'] ) && isset( $s['name'] ) ) {
                return 0 > strnatcmp( $f['name'], $s['name'] ) ? -1 : 1;
            }
            return 0;
        }

        // Sort by order.
        return ( $f_o < $s_o ) ? -1 : 1;

    }


}
