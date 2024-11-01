<?php
/**
 * Class VK_Adnetwork_Utils
 */
class VK_Adnetwork_Utils {
    /**
     * Merges multiple arrays, recursively, and returns the merged array.
     *
     * This function is similar to PHP's array_merge_recursive() function, but it
     * handles non-array values differently. When merging values that are not both
     * arrays, the latter value replaces the former rather than merging with it.
     *
     * Example:
     * $link_options_1 = array( 'fragment' => 'x', 'class' => array( 'a', 'b' ) );
     * $link_options_2 = array( 'fragment' => 'y', 'class' => array( 'c', 'd' ) );
     * // This results in array( 'fragment' => 'y', 'class' => array( 'a', 'b', 'c', 'd' ) ).
     *
     * @param array $arrays An arrays of arrays to merge.
     * @param bool  $preserve_integer_keys (optional) If given, integer keys will be preserved and merged instead of appended.
     * @return array The merged array.
     * @copyright Copyright 2001 - 2013 Drupal contributors. License: GPL-2.0+. Drupal is a registered trademark of Dries Buytaert.
     */
    public static function vk_adnetwork_merge_deep_array( array $arrays, $preserve_integer_keys = false ) {
        $result = [];
        foreach ( $arrays as $array ) {
            if ( ! is_array( $array ) ) {
                continue; }

            foreach ( $array as $key => $value ) {
                // Renumber integer keys as array_merge_recursive() does unless
                // $preserve_integer_keys is set to TRUE. Note that PHP automatically
                // converts array keys that are integer strings (e.g., '1') to integers.
                if ( is_integer( $key ) && ! $preserve_integer_keys ) {
                    $result[] = $value;
                } elseif ( isset( $result[ $key ] ) && is_array( $result[ $key ] ) && is_array( $value ) ) {
                    // recurse when both values are arrays.
                    $result[ $key ] = self::vk_adnetwork_merge_deep_array( [ $result[ $key ], $value ], $preserve_integer_keys );
                } else {
                    // otherwise, use the latter value, overriding any previous value.
                    $result[ $key ] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Convert array of html attributes to string.
     *
     * @param array $data attributes.
     * @return string
     * @since untagged
     */
    public static function vk_adnetwork_build_html_attributes( $data ) {
        $result = '';
        foreach ( $data as $_html_attr => $_values ) {
            if ( 'style' === $_html_attr ) {
                $_style_values_string = '';
                foreach ( $_values as $_style_attr => $_style_values ) {
                    if ( is_array( $_style_values ) ) {
                        $_style_values_string .= $_style_attr . ': ' . implode( ' ', array_filter( $_style_values ) ) . '; ';
                    } else {
                        $_style_values_string .= $_style_attr . ': ' . $_style_values . '; ';
                    }
                }
                $result .= " style=\"$_style_values_string\"";
            } else {
                if ( is_array( $_values ) ) {
                    $_values_string = esc_attr( implode( ' ', array_filter( $_values ) ) );
                } else {
                    $_values_string = esc_attr( $_values );
                }
                if ( $_values_string !== '' ) {
                    $result .= " $_html_attr=\"$_values_string\"";
                }
            }
        }
        return $result;
    }

    /**
     * Get inline asset.
     *
     * @param string $content existing content.
     * @return string $content
     */
    public static function vk_adnetwork_get_inline_asset( $content ) {
        // WP Fastest Cache Premium: "Render Blocking Js" feature.
        $content = ltrim( $content );
        if ( class_exists( 'WpFastestCache', false )
            && str_starts_with($content, '<script')) {
                $content = substr_replace( $content, '<script data-wpfc-render="false"', 0, 7 );
        }

        if ( VK_Adnetwork_Checks::active_autoptimize() || VK_Adnetwork_Checks::active_wp_rocket() ) {
            return '<!--noptimize-->' . $content . '<!--/noptimize-->';
        }
        return $content;
    }

    /**
     * Maybe translate a capability to a set of roles.
     *
     * @param string/array $roles_or_caps A set of roles or capabilities.
     * @return array $roles A list of roles.
     */
    public static function vk_adnetwork_maybe_translate_cap_to_role( $roles_or_caps ) {
        global $wp_roles;

        $roles_or_caps = (array) $roles_or_caps;
        $roles         = [];

        foreach ( $roles_or_caps as $cap ) {
            if ( $wp_roles->is_role( $cap ) ) {
                $roles[] = $cap;
                continue;
            }

            foreach ( $wp_roles->roles as $id => $role ) {
                if ( isset( $role['capabilities'][ $cap ] ) ) {
                    $roles[] = $id;
                }
            }
        }

        return array_unique( $roles );
    }

    /**
     * Get DateTimeZone object for the WP installation
     *
     * @return DateTimeZone DateTimeZone object.
     */
    public static function vk_adnetwork_get_wp_timezone() {
        static $date_time_zone;
        if ( ! is_null( $date_time_zone ) ) {
            return $date_time_zone;
        }

        // wp_timezone() is available since WordPress 5.3.0.
        if ( function_exists( 'wp_timezone' ) ) {
            $date_time_zone = wp_timezone();

            return $date_time_zone;
        }

        $time_zone = get_option( 'timezone_string' );
        // no timezone string but gmt offset.
        if ( empty( $time_zone ) ) {
            $time_zone = get_option( 'gmt_offset' );
            // gmt + x but not prefixed with a "+".
            if ( preg_match( '/^\d/', $time_zone ) ) {
                $time_zone = '+' . $time_zone;
            }
        }

        $date_time_zone = new DateTimeZone( $time_zone );

        return $date_time_zone;
    }

    /**
     * Get literal expression of timezone.
     *
     * @return string Human readable timezone name.
     */
    public static function vk_adnetwork_get_timezone_name() {
        $time_zone = self::vk_adnetwork_get_wp_timezone()->getName();
        if ( $time_zone === 'UTC' ) {
            return 'UTC+0';
        }

        if ( str_starts_with($time_zone, '+') || str_starts_with($time_zone, '-')) {
            return 'UTC' . $time_zone;
        }

        // translators: time zone name.
        return sprintf( esc_html__( 'time of %s', 'vk-adnetwork' ), $time_zone );
    }

    public static function vk_adnetwork_wphost () {
        $wphost = wp_parse_url(get_option('siteurl'), PHP_URL_HOST);
        // хинт для ЛОКАЛХОСТ -- чтобы не было ошибки от МТ: `URL should point to a working resource`
        $wphost .= strpos($wphost, '.') ? '' : '.RU';
        return $wphost;
    }

    public static function vk_adnetwork_pad_style($format_id = 109513) {
        $style = [
//            86883 => [ // 240x400
//                'block__backgroundColor' => 'white',
//                'block__borderColor' => '#ededed',
//                'block__borderStyle' => 'solid',
//                'block__borderWidth' => '1px',
//                'button__backgroundColor' => '#00abf1',
//                'button__color' => 'white',
//                'button__fontFamily' => 'Arial',
//                'text__color' => 'black',
//                'text__fontFamily' => 'Arial',
//                'titleHover__color' => 'black',
//                'title__color' => 'black',
//                'title__fontFamily' => 'Arial',
//                'title__textDecorationLine' => 'none',
//                ],
            109513 => [ // 970x250
                'block__backgroundColor' => 'white',
                'block__borderColor' => '#ededed',
                'block__borderStyle' => 'solid',
                'block__borderWidth' => '1px',
                'button__backgroundColor' => '#00abf1',
                'button__color' => 'white',
                'button__fontFamily' => 'Arial',
                'button__fontSize' => '12px',
                'domainHover__color' => '#999',
                'domain__color' => '#999',
                'domain__fontFamily' => 'Arial',
                'domain__textDecorationLine' => 'none',
                'text__fontFamily' => 'Arial',
                'title__fontFamily' => 'Arial',
                'title__textDecorationLine' => 'none',
                'domain__fontSize' => '14px',               //     12 x 14
                'text__color' => '#000',                    // 4A4A4A x 000
                'text__fontSize' => '16px',                 //     14 x 16
                'titleHover__color' => '#000',              // 4A4A4A x 000
                'title__color' => '#000',                   // 4A4A4A x 000
                'title__fontSize' => '20px',                //     16 x 20
            ],
            103888 => [ // 300x600
                'block__backgroundColor' => 'white',
                'block__borderColor' => '#ededed',
                'block__borderStyle' => 'solid',
                'block__borderWidth' => '1px',
                'button__backgroundColor' => '#00abf1',
                'button__color' => 'white',
                'button__fontFamily' => 'Arial',
                'button__fontSize' => '12px',
                'domainHover__color' => '#999',
                'domain__color' => '#999',
                'domain__fontFamily' => 'Arial',
                'domain__textDecorationLine' => 'none',
                'text__fontFamily' => 'Arial',
                'title__fontFamily' => 'Arial',
                'title__textDecorationLine' => 'none',
                'domain__fontSize' => '12px',               //     12 x 14
                'text__color' => '#4A4A4A',                 // 4A4A4A x 000
                'text__fontSize' => '14px',                 //     14 x 16
                'titleHover__color' => '#4A4A4A',           // 4A4A4A x 000
                'title__color' => '#4A4A4A',                // 4A4A4A x 000
                'title__fontSize' => '16px',                //     16 x 20
            ],
            103887 => [ // 300x250
                'block__backgroundColor' => 'white',
                'block__borderColor' => '#ededed',
                'block__borderStyle' => 'solid',
                'block__borderWidth' => '1px',
                'button__color' => 'white',
                'button__backgroundColor' => '#00abf1',
                'button__fontFamily' => 'Arial',
                'text__fontFamily' => 'Arial',
                'text__color' => 'black',
                'title__fontFamily' => 'Arial',
                'title__textDecorationLine' => 'none',
                'title__color' => 'black',
                'titleHover__color' => 'black',
            ],
        ];
        return $style[$format_id];
    }

    public static function vk_adnetwork_create_pad_WxH ($format_id = 1524836, $title = '') {
        $f_WxH = [
            1524836 => 'inPage',
             109513 => '970x250',
             103888 => '300x600',
             103887 => '300x250',
//              86883 => '240x400',
        ];
        $wphost = self::vk_adnetwork_wphost();
        $b = date('Y-m-d H:i'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $return = [
            'description'                   => $title ?: "WP: $wphost $f_WxH[$format_id] / $b",
            'format_id'                     => $format_id,
            'shows_period'                  => 'day',
            'shows_limit'                   => null,
            'shows_interval'                => null,
            'integration_type'              => 'js_sdk',
            'partner_a_block_cpm_limit'     => 0,
            'partner_a_cpm_limit_geo'       => new stdClass(),              // {}, а не []
        ];
        if ($format_id == 1524836) {
            $return['filters'] = ['allow_image_types' => ['static', 'video']];
        }else{
            $return['filters'] = new stdClass(); // {}, а не []
            $return['style'] = self::vk_adnetwork_pad_style($format_id);
            $return['dummy_html'] = '';
        }
        return $return;
    }

     /**
     * создание пада-массива - для изготовления джейсона -- для ПОСТа в МТ
     * вызывается в:
     *      self::vk_adnetwork_group_pads_post        -- создание площадки и блока в ней
     *      self::vk_adnetwork_group_pads_pads_post   -- создание/редактирование блока в площадке
     */
    public static function vk_adnetwork_create_pad ($width = '900', $height = '250', $title = null) {
        if (! $title) {
            $wphost = self::vk_adnetwork_wphost();
            $b = date('Y-m-d H:i'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            $title = "WP: $wphost / $b";
        }
        $banners_count = '3';
        return [
            'description'                   => $title,
            'format_id'                     => 1356766,                     // адаптив-веб?
            'filters'                       => new stdClass(),              // {}, а не []
            'shows_period'                  => 'day',
            'shows_limit'                   => null,
            'shows_interval'                => null,
            'style' => [
                'block__width'              => $width . 'px',
                'block__height'             => $height . 'px',
                'block__bannerCount'        => $banners_count,
                'countEmbed__width'         => $width . '',
                'countEmbed__height'        => $height . '',

                'block__borderStyle'        => 'solid',
                'block__borderColor'        => '#e5e5e5',
                'block__borderWidth'        => '1px',
                'block__backgroundColor'    => 'white',
                'blockItem__marginTop'      => '0px',
                'blockItem__padding'        => '10px',
                'text__fontFamily'          => 'Arial',
                'text__fontSize'            => '12px',
                'text__color'               => '#666',
                'title__fontFamily'         => 'Arial',
                'title__fontSize'           => '15px',
                'title__textDecorationLine' => 'none',
                'title__color'              => '#000',
                'titleHover__color'         => '#000',
                'countEmbed__type'          => 'adaptive',
            ],
            'banners_count'                 => $banners_count,              // кол-во баннеров в блоке
            'integration_type'              => 'js_sdk',
            'partner_a_block_cpm_limit'     => 0,
            'partner_a_cpm_limit_geo'       => new stdClass(),              // {}, а не []
            'dummy_html'                    => '',
            'comment'                       => '',                          // Опишите шаги которые нам нужно сделать чтобы увидеть рекламу
            'settings' => [
                'block_width'               => $width . 'px',
                'block_height'              => $height . 'px',
            ],
        ];

    }

     /**
     * создание блока в площадке -- с дефолтными опциями                        ($pad_id = '')
     * https://target.my.com/doc/api/ru/resource/GroupPadPads
     * + редактирование блока в площадке -- с м.б. другой шириной и высотой    ($pad_id <> '')
     * https://target.my.com/doc/api/ru/resource/GroupPadPad
     */
    public static function vk_adnetwork_group_pads_pads_post ($format_id = 1524836, $title = '', $pad_id = '') {
        $options = VK_Adnetwork::get_instance()->options();
        $token    = $options['vk-adnetwork-creds']['access_token'] ?? '';
        $group_id = $options['group_id']                 ?? '';
        if (!$token || !$group_id) return [];
        if ($pad_id) $pad_id = "/$pad_id"; // /api/v2/group_pads/<>/pads.json vs /api/v2/group_pads/<>/pads/<>.json
        $url = VK_ADNETWORK_URL . "api/v2/group_pads/$group_id/pads$pad_id.json";
        // $create_pad = self::vk_adnetwork_create_pad($width, $height, $title);
        $create_pad = self::vk_adnetwork_create_pad_WxH($format_id, $title); // { -x- adaptive self::vk_adnetwork_create_pad($width, $height); }
        $create_pad = wp_json_encode($create_pad);
        $data = self::vk_adnetwork_curl($url, $token, $create_pad); // -x- ,JSON_FORCE_OBJECT = все {}
        // echo "<!--vk_adnetwork_group_pads_pads_post (access_token, group_id)\nAuthorization: Bearer $access_token\n$create_pad\n\n$data\n-->";
        if (!isset($data['id']) && isset($data['error'])
            && ($data['error']['code'] ?? '') !== 'validation_failed' // bad_value: The specified height are less than the minimum allowable: 250px
        )   // reauth НЕ нужен, если validation_failed -- т.к. ошибка не с протухшим токеном (а с размерами меньше 250х250)
            $data = self::vk_adnetwork_reauth($url, $create_pad); // POST!!
        return $data; // тут есть только ид=1234567 и больше ничего
    }

    /**
     *      * < class-vk-adnetwork-admin.redirect_after_admin_action_update
     *
     * создание площадки и блока в ней -- с дефолтными опциями
     * https://target.my.com/doc/api/ru/resource/GroupPads
     */
    public static function vk_adnetwork_group_pads_post ($p = '') {

        $url = VK_ADNETWORK_URL . "api/v2/group_pads.json$p";
        $wphost = self::vk_adnetwork_wphost();
        $token = VK_Adnetwork::get_instance()->options()['vk-adnetwork-creds']['access_token'];

        $create_group_pads = [
            'url'                              => $wphost,
            'description'                      => "WP: $wphost",                    // ? Описание площадки можем оставить пустым
            'pads'                             => [ self::vk_adnetwork_create_pad_WxH() ],    // 1524836 -x- { 109513 = 970x250 }{ -x- adaptive [ self::vk_adnetwork_create_pad() ], }
            'platform_id'                      => 6185,                             // десктоп?
            'filters' => [
                'deny_mobile_android_category' => [],
                'deny_mobile_category'         => [],
                'deny_iab_category'            => [],
                'allow_iab_category'           => [],
                'deny_topics'                  => [],
                'deny_pad_url'                 => [],
                'deny_mobile_apps'             => [],
            ],
            'create_mytracker'                 => false,
        ];
        $data = self::vk_adnetwork_curl($url, $token, wp_json_encode($create_group_pads)); // -x- ,JSON_FORCE_OBJECT = все {}
        return $data;   // тут есть только ид=1234567 и больше ничего
    }

    /**
     * < class-vk-adnetwork-admin.redirect_after_admin_action_update
     *
     * код вставки рекламы из vk_adnetwork-setup.xml с заменами {SLOT_ID} на $slot_id +++
     * пример: [delivering_pads] => '1563062,1573258,1584249,1598238,1598277', [delivering] => Array, [not_delivering] => Array, [test_delivering] => Array
     */
    public static function vk_adnetwork_setup_xml ($group_id) {
        $group_pad = self::vk_adnetwork_group_pads($group_id); // после создания площадки - в ней один пад
        $group = $group_pad['group_pads'][$group_id];
        // а в ней первый пад, а в нём slot_id и pad_id
        $pad = array_values($group['pads'])[0];
        $post_ids = [0];
        if (isset($pad['slot_id']) && isset($pad['id']) && isset($pad['description'])
            && isset($pad['format_id'])
//            && isset($pad['style']['block__width']) && isset($pad['style']['block__height'])
//            && isset($pad['style']['countEmbed__width']) && isset($pad['style']['countEmbed__height'])
        ) {
            // К.К. настойчиво просит создавать именно -- рекламу-черновик, чтобы юзер её самостоятельно опубликовал, поэтому
            // <post_status type="string">draft</post_status>, а не <post_status type="string">publish</post_status> -- в файле vk_adnetwork-setup.xml
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            $xml = file_get_contents( VK_ADNETWORK_BASE_PATH . 'admin/assets/xml/'
                . ( $pad['format_id'] == 1524836
                    ? 'vk_adnetwork-inpage-setup.xml'    // {slot_id}, {id}, {title_ad}
                    : 'vk_adnetwork-setup.xml'           // {slot_id}, {id}, {title_ad}, {block__height}, {block__width}, {countEmbed__height}, {countEmbed__width}
                  ) );
            $f_WxH = [
                1524836 => [0,0,0,0],
                 109513 => ['970px', '250px', 970, 250], // {block__height}, {block__width}, {countEmbed__height}, {countEmbed__width}
//                 103888 => ['300px', '600px', 300, 600],
//                  86883 => ['240px', '400px', 240, 400],
            ];
            $the_wh = $f_WxH[$pad['format_id']] ?? [0,0,0,0];
            $xml = str_replace( // WP: domen.ru 970x250 / 2023-12-07 17:34        970px             250px                    970                    250
                [    '{id}',     '{slot_id}',               '{title_ad}',    '{block__width}', '{block__height}', '{countEmbed__width}', '{countEmbed__height}', ],
                [$pad['id'], $pad['slot_id'], esc_attr($pad['description']), $the_wh[0],       $the_wh[1],        $the_wh[2],            $the_wh[3],             ],
//              [$pad['id'], $pad['slot_id'], $pad['style']['block__width'], $pad['style']['block__height'], $pad['style']['countEmbed__width'], $pad['style']['countEmbed__height'], $pad['description']], // " ($group_id-pad:$pad[id]-slot:$pad[slot_id])"
                $xml
            );
            $post_ids = VK_Adnetwork_Import::get_instance()->import( $xml );
        }else{
            // Ошибка! Нет slot_id
            return ['message' => esc_html__('Error! No slot_id', 'vk-adnetwork')];
        }
        return ['post_id' => $post_ids[0], 'group_id' => $group_id, 'slot_id' => $pad['slot_id'], 'pad_id' => $pad['id'], 'message' => 'vk-adnetwork-starter-setup-success'];
    }

    /**
     *  ((Страница редактирования объявления))
     * < ad.save // все пады площадки -- ищем новый [id]
     * < ad_type_plain.render_parameters // все пады площадки -- будем делать select (выпадушку в редактировании объявления) из них
     *  ((Первое сохранение - после создания площадки - в ней один пад + редирект на список))
     * < vk_adnetwork_setup_xml // после создания площадки - в ней один пад
     *  ((Панель_Управления - Дашборд - графики)) render_graph
     * < vk_adnetwork_group_stat_pads // собираем НЕ ВСЕ пады (которые delivering) а только $group_id
     * < vk_adnetwork_group_stat_pads // тексты про площадки из МТ
     *
     * curl to  https://ads.vk.com/api/v2/group_pads.json?fields=description,id,url,status&limit=100
     *          https://ads.vk.com/api/v2/group_pads/1563061.json?limit=100
     * https://target.my.com/doc/api/ru/resource/GroupPad
     * все пады (все пады площадки плюс высота-ширина)
     * // пример: [delivering_pads] => '1563062,1573258,1584249,1598238,1598277', [delivering] => Array, [not_delivering] => Array, [test_delivering] => Array
     */
    public static function vk_adnetwork_group_pads ($group_id = '', $fields = 'description,id,url,status,pads,delivery,issues,pads__id,pads__slot_id,pads__format_id,pads__status,pads__description,pads__style,pads__delivery', $limit = 100) {
        $options = VK_Adnetwork::get_instance()->options();
        $token ??= $options['vk-adnetwork-creds']['access_token']  ?? '';
        if (!$token) return [];
        $is_numeric_group_id = is_numeric($group_id);
        $_group_id = $group_id ? "/$group_id" : ''; // /api/v2/group_pads.json vs /api/v2/group_pads/1589452.json
        $url = VK_ADNETWORK_URL . "api/v2/group_pads$_group_id.json?fields=$fields&limit=$limit";
        // if ($echo) echo " <a target='_blank' href='$url'>[mt/api/group_pads]</a> ";
        $data = self::vk_adnetwork_curl($url, $token); // -x- POST
        if (!isset($data)) return ''; // нет интернета? НЕ ЛОМАЕМСЯ!
        if (!isset($data['items']) && isset($data['error'])) {
            $data = self::vk_adnetwork_reauth($url);
            if (!isset($data['items']) && isset($data['error'])) return '';
            $options = VK_Adnetwork::get_instance()->options();
            // раз мы сделали vk_adnetwork_reauth, то access_token уже ДРУГОЙ, и его надо не перезатереть старым в конце -- в update_options()
        }

        if (!$is_numeric_group_id) return ['group_pads' => $data['items']];
        // формат разный! у https://ads.vk.com/api/v2/group_pads/1589452.json и https://ads.vk.com/api/v2/group_pads.json
        $items = $group_id ? [$data] : $data['items']; // всегда будет [$data] -- нет вызовов без $group_id
        $delivering_pads = [];
        foreach ($items as $item) {
            $delivery = $item['delivery'];
            $issues = [];
            if (isset($item['issues']))
                foreach ($item['issues'] as $issue)
                    $issues[$issue['code']] = $issue['message'];
            $pads = [];
            if (isset($item['pads']))             // -x- format_id=1356766,  comment='', banners_count=3,
                foreach ($item['pads'] as $pad)   // description=WP: otdyh-baikal.ru b#1,status=active, ...
                    $pads[$pad['id']] = $pad;     // $pad[style][block__width]=900px, [block__height]=250px, ... -x- array_merge($pad, $padstyle[$pad['id']] ?? []);
            if ($delivery === 'delivering') array_push($delivering_pads, ...array_keys($pads));

            $items2['group_pads'][$item['id']] = $items2[$delivery][$item['id']] = array_merge($item, ['issues' => $issues, 'pads' => $pads]); // description url status ...
        }
        $items2['delivering_pads'] = join(',', $delivering_pads) ?: null;
        // if ($echo) { echo "<!--vk_adnetwork_group_pads($group_id)\n"; print_r($items2); echo '-->'; }  // пример - scratch_345.pod // Warning: Cannot modify header information - headers already sent by (output started at ../utils.php:511) in ../wp-includes/pluggable.php on line 1430
        $group = $items2['group_pads'][$group_id];
        $options['moderation'] = [ // сохраним статусы и проблемы площадки, чтобы не запрашивать на каждой странице их снова
            'time'     => time(),               // будем освежать статус модерации, если прошло больше ... 10 мин?
            'status'   => $group['status'],     // active | blocked | deleted
            'delivery' => $group['delivery'],   // delivering | test_delivering | not_delivering
            'issues'   => $group['issues'],     // [GROUP_PAD_ON_MODERATION] => The GroupPad is on moderation. [NO_ACTIVE_PADS] => The GroupPad has no active pads.
        ];
        VK_Adnetwork::get_instance()->update_options($options);
        return $items2;
    }
    // пример: [delivering_pads] => '1563062,1573258,1584249,1598238,1598277', [delivering] => Array, [not_delivering] => Array, [test_delivering] => Array

    /*
     * < class-overview-widgets.render_graph (7)
     *
     * курлом тянем ads.vk.com/api/v2/statistics/partner/pads/day.json за $days дней -- 1, 2 или 3 раза (поэтому _x3)
     * если токен протух -- пробуем отрефрешить (и запишем в опции плагина)
     * если не удаётся отрефрешить -- пробуем создать токен (из клиент-ид и секрет) (и запишем в опции плагина)
     * если и так не удаётся -- печатаем оба ответа МТ с ошибками и возвращаем []
     * $data = '{"items":[{"id":946497,"rows":[{"date":"2023-03-01","currency":"RUB","shows":0,"clicks":0,"goals":0,"ctr":0,"requests":59,"requested_banners":177,"responsed_blocks":56,"responses":56,"noshows":121,"amount":"0","cpm":"0","fill_rate":94.91525423728814,"show_rate":0,"vtr":0,"vr":0,"render":0,"win_notice":56,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":100,"loss_rate":0},{"date":"2023-03-02","currency":"RUB","shows":1,"clicks":0,"goals":0,"ctr":0,"requests":93,"requested_banners":259,"responsed_blocks":80,"responses":80,"noshows":179,"amount":"0.04","cpm":"35.99","fill_rate":86.02150537634408,"show_rate":1.25,"vtr":0,"vr":0,"render":0,"win_notice":74,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":92.5,"loss_rate":0},{"date":"2023-03-03","currency":"RUB","shows":0,"clicks":0,"goals":0,"ctr":0,"requests":112,"requested_banners":296,"responsed_blocks":63,"responses":63,"noshows":233,"amount":"0","cpm":"0","fill_rate":56.25,"show_rate":0,"vtr":0,"vr":0,"render":0,"win_notice":32,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":50.79365079365079,"loss_rate":0},{"date":"2023-03-04","currency":"RUB","shows":0,"clicks":0,"goals":0,"ctr":0,"requests":125,"requested_banners":375,"responsed_blocks":108,"responses":108,"noshows":267,"amount":"0","cpm":"0","fill_rate":86.4,"show_rate":0,"vtr":0,"vr":0,"render":0,"win_notice":93,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":86.11111111111111,"loss_rate":0},{"date":"2023-03-05","currency":"RUB","shows":1,"clicks":0,"goals":0,"ctr":0,"requests":98,"requested_banners":268,"responsed_blocks":85,"responses":85,"noshows":183,"amount":"0.1","cpm":"99.5","fill_rate":86.73469387755102,"show_rate":1.1764705882352942,"vtr":0,"vr":0,"render":0,"win_notice":85,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":100,"loss_rate":0},{"date":"2023-03-06","currency":"RUB","shows":0,"clicks":0,"goals":0,"ctr":0,"requests":362,"requested_banners":1086,"responsed_blocks":352,"responses":352,"noshows":734,"amount":"0","cpm":"0","fill_rate":97.23756906077348,"show_rate":0,"vtr":0,"vr":0,"render":0,"win_notice":345,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":98.01136363636364,"loss_rate":0},{"date":"2023-03-07","currency":"RUB","shows":0,"clicks":0,"goals":0,"ctr":0,"requests":348,"requested_banners":1044,"responsed_blocks":333,"responses":333,"noshows":711,"amount":"0","cpm":"0","fill_rate":95.6896551724138,"show_rate":0,"vtr":0,"vr":0,"render":0,"win_notice":329,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":98.7987987987988,"loss_rate":0},{"date":"2023-03-08","currency":"RUB","shows":0,"clicks":0,"goals":0,"ctr":0,"requests":56,"requested_banners":168,"responsed_blocks":48,"responses":48,"noshows":120,"amount":"0","cpm":"0","fill_rate":85.71428571428571,"show_rate":0,"vtr":0,"vr":0,"render":0,"win_notice":43,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":89.58333333333334,"loss_rate":0},{"date":"2023-03-09","currency":"RUB","shows":0,"clicks":0,"goals":0,"ctr":0,"requests":258,"requested_banners":704,"responsed_blocks":249,"responses":249,"noshows":455,"amount":"0","cpm":"0","fill_rate":96.51162790697676,"show_rate":0,"vtr":0,"vr":0,"render":0,"win_notice":237,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":95.18072289156626,"loss_rate":0},{"date":"2023-03-10","currency":"RUB","shows":0,"clicks":0,"goals":0,"ctr":0,"requests":338,"requested_banners":1014,"responsed_blocks":320,"responses":320,"noshows":694,"amount":"0","cpm":"0","fill_rate":94.67455621301775,"show_rate":0,"vtr":0,"vr":0,"render":0,"win_notice":262,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":81.875,"loss_rate":0}],"total":{"shows":2,"clicks":0,"goals":0,"ctr":0,"requests":1849,"requested_banners":5391,"responsed_blocks":1694,"responses":1694,"noshows":3697,"amount":"0.14","cpm":"67.75","fill_rate":91.6170903190914,"show_rate":0.11806375442739078,"vtr":0,"vr":0,"render":0,"win_notice":1556,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":91.85360094451003,"loss_rate":0}}],"total":{"shows":2,"clicks":0,"goals":0,"ctr":0,"requests":1849,"requested_banners":5391,"responsed_blocks":1694,"responses":1694,"noshows":3697,"amount":"0.14","cpm":"67.75","fill_rate":91.6170903190914,"show_rate":0.11806375442739078,"vtr":0,"vr":0,"render":0,"win_notice":1556,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":91.85360094451003,"loss_rate":0}}';
     * $data = '{"items":[{"id":1236493,"rows":[{"date":"2023-03-16","currency":"RUB","shows":3524527,"clicks":17016,"goals":2054,"ctr":0.48278818689713543,"requests":23417784,"requested_banners":93395178,"responsed_blocks":20631064,"responses":20631064,"noshows":72764114,"amount":"171293.70001","cpm":"48.6004788756051521","fill_rate":88.09998418296112,"show_rate":17.083592974167498,"vtr":5.186559697283924,"vr":100,"render":0,"win_notice":17549745,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":85.06466268535641,"loss_rate":0},{"date":"2023-03-17","currency":"RUB","shows":4291823,"clicks":17451,"goals":1721,"ctr":0.4066104310452691,"requests":23804345,"requested_banners":94905595,"responsed_blocks":17426720,"responses":17426720,"noshows":77478875,"amount":"201989.40042","cpm":"47.0637769591150427","fill_rate":73.20814750416363,"show_rate":24.627830136709605,"vtr":5.526240298550671,"vr":100,"render":0,"win_notice":14111433,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":80.97584054830743,"loss_rate":0},{"date":"2023-03-18","currency":"RUB","shows":5540539,"clicks":18201,"goals":1555,"ctr":0.328505944999214,"requests":27195665,"requested_banners":108397125,"responsed_blocks":12704622,"responses":12704622,"noshows":95692503,"amount":"264205.91148","cpm":"47.6859582578518083","fill_rate":46.71561441869504,"show_rate":43.61041989285474,"vtr":6.235984578483759,"vr":100,"render":0,"win_notice":8434993,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":66.39310480862791,"loss_rate":0},{"date":"2023-03-19","currency":"RUB","shows":5066473,"clicks":18262,"goals":1525,"ctr":0.3604479881763902,"requests":30821019,"requested_banners":122855836,"responsed_blocks":11522953,"responses":11522953,"noshows":111332883,"amount":"237016.57101","cpm":"46.7813745400399844","fill_rate":37.38667108962231,"show_rate":43.96852959480091,"vtr":6.613101629363996,"vr":100,"render":0,"win_notice":7426777,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":64.45202892001728,"loss_rate":0},{"date":"2023-03-20","currency":"RUB","shows":4746993,"clicks":17094,"goals":1247,"ctr":0.360101647506116,"requests":28957079,"requested_banners":115418708,"responsed_blocks":11182064,"responses":11182064,"noshows":104236644,"amount":"225733.66726","cpm":"47.5529808575660423","fill_rate":38.61599438258258,"show_rate":42.45184967641036,"vtr":6.252281607169305,"vr":100,"render":0,"win_notice":6993206,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":62.53949181474905,"loss_rate":0},{"date":"2023-03-21","currency":"RUB","shows":5275335,"clicks":17297,"goals":1335,"ctr":0.32788439028042765,"requests":28290918,"requested_banners":112823508,"responsed_blocks":12290873,"responses":12290873,"noshows":100532635,"amount":"250784.4464","cpm":"47.539056078903046","fill_rate":43.444588825290154,"show_rate":42.920751032086976,"vtr":6.3015203875722,"vr":100,"render":0,"win_notice":8124689,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":66.1034330108203,"loss_rate":0},{"date":"2023-03-22","currency":"RUB","shows":5752269,"clicks":18470,"goals":1293,"ctr":0.32109068612750896,"requests":27808727,"requested_banners":110871369,"responsed_blocks":13288308,"responses":13288308,"noshows":97583061,"amount":"272161.98427","cpm":"47.3138485474166803","fill_rate":47.784668460372174,"show_rate":43.28819741384682,"vtr":6.037233754854342,"vr":100,"render":0,"win_notice":9070824,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":68.26169291079044,"loss_rate":0},{"date":"2023-03-23","currency":"RUB","shows":5235231,"clicks":18627,"goals":1819,"ctr":0.35580091881332454,"requests":26083173,"requested_banners":104024530,"responsed_blocks":17953437,"responses":17953437,"noshows":86071093,"amount":"260629.12814","cpm":"49.7836920930518634","fill_rate":68.83149147536612,"show_rate":29.160048853041342,"vtr":6.031996011380229,"vr":100,"render":0,"win_notice":13838317,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":77.07892923232471,"loss_rate":0},{"date":"2023-03-24","currency":"RUB","shows":4710506,"clicks":21022,"goals":2628,"ctr":0.44627901970616324,"requests":25108978,"requested_banners":100159857,"responsed_blocks":24749420,"responses":24749420,"noshows":75410437,"amount":"258846.26892","cpm":"54.9508415698865472","fill_rate":98.56801021531024,"show_rate":19.03279349576677,"vtr":6.052201888501467,"vr":100,"render":0,"win_notice":21558743,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":87.108073643746,"loss_rate":0},{"date":"2023-03-25","currency":"RUB","shows":5037397,"clicks":21070,"goals":2613,"ctr":0.4182715795479292,"requests":25642837,"requested_banners":102260838,"responsed_blocks":25318646,"responses":25318646,"noshows":76942192,"amount":"289062.20053","cpm":"57.383247842089873","fill_rate":98.7357444108076,"show_rate":19.89599680804416,"vtr":6.101325804341777,"vr":100,"render":0,"win_notice":22082396,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":87.2179183673566,"loss_rate":0},{"date":"2023-03-26","currency":"RUB","shows":5897537,"clicks":25440,"goals":3520,"ctr":0.4313665179209558,"requests":28728776,"requested_banners":114577171,"responsed_blocks":28356585,"responses":28356585,"noshows":86220586,"amount":"319348.07255","cpm":"54.1493970364238495","fill_rate":98.70446621185671,"show_rate":20.797768842757336,"vtr":6.135221333222641,"vr":100,"render":0,"win_notice":24975202,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":88.07549287052726,"loss_rate":0}],"total":{"shows":55078630,"clicks":209950,"goals":21310,"ctr":0.3811823206205383,"requests":295859301,"requested_banners":1179689715,"responsed_blocks":195424692,"responses":195424692,"noshows":984265023,"amount":"2751071.35099","cpm":"49.9480715295569262","fill_rate":66.05325279261713,"show_rate":28.18406898141613,"vtr":6.059500878544963,"vr":100,"render":0,"win_notice":154166325,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":78.88784340517215,"loss_rate":0}}],"total":{"shows":55078630,"clicks":209950,"goals":21310,"ctr":0.3811823206205383,"requests":295859301,"requested_banners":1179689715,"responsed_blocks":195424692,"responses":195424692,"noshows":984265023,"amount":"2751071.35099","cpm":"49.9480715295569262","fill_rate":66.05325279261713,"show_rate":28.18406898141613,"vtr":6.059500878544963,"vr":100,"render":0,"win_notice":154166325,"confirmed_win_notice":0,"loss_notice":0,"display_rate":0,"win_rate":78.88784340517215,"loss_rate":0}}';
     * пример: [delivering_pads] => '1563062,1573258,1584249,1598238,1598277', [delivering] => Array, [not_delivering] => Array, [test_delivering] => Array
     */
    public static function vk_adnetwork_group_stat_pads ($days = 7) {
        $options = VK_Adnetwork::get_instance()->options();
        $group_id = $options['group_id'] ?? '';
        $items = self::vk_adnetwork_group_pads($group_id); // собираем НЕ ВСЕ пады (которые delivering) а только $group_id
        $items['delivering_pads'] ??= '';
        $delivering_group_pads = $items['delivering_pads']
            ? $group_id
            : $group_id;
                // это было вместо : $group_id; -- // \|/ 11+15 площадок с ненулевыми графиками (если наша площадка не диливерит - то хоть какие-то графики нарисуем)
                // '1563061,1586071,1598554,1616165,1617883,1617902,1618123,1619479,1622699,1623247,1626029'         // это ГРУППЫ падов test.partner.target@mail.ru
                // . ',53923,53939,53940,54018,54019,54020,54062,54063,54064,54066,54067,54068,54069,54070,92165'; // это ГРУППЫ падов Камиля, если его ид/секрет а не TEST.PARTNER.TARGET@MAIL.RU
        $data  = self::vk_adnetwork_stat_pads($days, $items['delivering_pads'] ?: $delivering_group_pads); // получаем графики за $days дней по идам из списка delivering_pads
        $txts  = $items['delivering_pads'] // тексты про площадки из МТ
            ? $items // если для НАШЕЙ площадки есть графики -- то не будем еще раз запрашивать -- всё уже есть из предыдущего вызова vk_adnetwork_group_pads()
            : self::vk_adnetwork_group_pads($delivering_group_pads, 'description,id,url,status,delivery'); // это ЛАЖА (для -- наша площадка не диливерит - то хоть какие-то графики нарисуем)
        if ($txts && isset($txts['group_pads']) && is_array($txts['group_pads']))
            foreach ($txts['group_pads'] as $group_pad) {
                $id_txt[$group_pad['id']] = $group_pad;     // не для нашей площадки, а по списку выше -- нужны тексты для ПЛОЩАДОК
                if (isset($group_pad['pads']))              // а для НАШЕЙ площадки -- нужны тексты для ПАДОВ
                    foreach ($group_pad['pads'] as $pad)
                        $id_txt[$pad['id']] = $pad;
            }
        if ($data && isset($data['items']) && is_array($data['items']))
            foreach ($data['items'] as &$item)
                $item['txt'] = $id_txt[$item['id']] ?? [];
        return ['graph' => $data, 'deliverygroups' => $items];
    }

    /**
     * < vk_adnetwork_group_stat_pads // получаем графики за $days дней по идам из списка delivering_pads
     *   < class-overview-widgets.render_graph (7)
     *
     * curl to https://ads.vk.com/api/v2/statistics/partner/pads/day.json
     * https://ads.vk.com/help/partners/web/reporting_api_statistics/ru
     * статистика по всем падам за 7 дней
     */
    public static function vk_adnetwork_stat_pads ($days = 7, $group = '') {
        $options = VK_Adnetwork::get_instance()->options();
        $token ??= $options['vk-adnetwork-creds']['access_token']  ?? '';
        if (!$token) return [];

        $date_from = date('Y-m-d', strtotime("-$days days"));   // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $date_to   = date('Y-m-d', time());                     // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $microtime = ceil(microtime(1)*1000);
        // $pads_base  = "https://ads.vk.com/api/v2/statistics/partner/pads_base/day.json?date_from=$date_from&date_to=$date_to&id=$id"; // короткие строки
        // -X- (?) &exact_money=1
        $url = VK_ADNETWORK_URL . "api/v2/statistics/partner/pads/day.json?date_from=$date_from&date_to=$date_to&id=$group&_=$microtime";
        // echo " <a target='_blank' href='$pads'>[mt/api/day]</a> ";

        $data = self::vk_adnetwork_curl($url, $token); // -x- POST
        if (!isset($data['items']) && isset($data['error']))
            $data = self::vk_adnetwork_reauth($url); //  && $data['error']['code'] == 'expired_token' // code => invalid_token, message => Unknown access token
        return $data;
    }


    /**
     * < vk_adnetwork_group_pads_pads_post : $data = self::vk_adnetwork_reauth($url, $create_pad); // POST!!
     * < vk_adnetwork_group_pads           : $data = self::vk_adnetwork_reauth($url);
     * < vk_adnetwork_stat_pads            : $data = self::vk_adnetwork_reauth($url);
     *
     * self::vk_adnetwork_oauth2_token if error
     * return <access_token>|0
     * error => invalid_token, error_description => Access token has been deleted
     *
     */
    public static function vk_adnetwork_reauth ($url, $post = null) { // ($cURLConnection) {
        $options = VK_Adnetwork::get_instance()->options();
        $new_token = '';
        if ($options['vk-adnetwork-creds'] && $options['vk-adnetwork-creds']['client_id'] && $options['vk-adnetwork-creds']['client_secret']) {
            $tokens = self::vk_adnetwork_oauth2_token('refresh_token', $options);
            if (isset($tokens['access_token']) && !isset($tokens['error'])) {
                $new_token = $tokens['access_token'];
            }else{ // попробуем еще client_credentials вместо refresh_token -- м.б. на ДРУГОМ вордпрессе удалил токены, например -- тогда получим СЛЕДУЮЩИЙ (из 5!)
                $tokens2 = self::vk_adnetwork_oauth2_token('client_credentials', $options);
                if (isset($tokens2['access_token']) && !isset($tokens2['error'])) {
                    $new_token = $tokens2['access_token'];
                }else if (isset($tokens2['error']) && $tokens2['error'] == 'token_limit_exceeded') { // вобля! переделать бы надо бы
                    self::vk_adnetwork_oauth2_token('', $options); // удаление всех токенов
                    $tokens2 = self::vk_adnetwork_oauth2_token('client_credentials', $options);
                    if (isset($tokens2['access_token']) && !isset($tokens2['error'])) {
                        $new_token = $tokens2['access_token'];
                    }else{
                        return $tokens2;
                    }
                }else{
                    // echo "<b>Двойная ошибка обновления токена!</b>\n<pre>\nrefresh_token = "; var_dump($tokens); echo "\nclient_credentials = "; var_dump($tokens2); echo '</pre>';
                    return $tokens2;
                }
            }
            $data = self::vk_adnetwork_curl($url, $new_token, $post);
            return $data;

        }
        return [];
    }

    /**
     * < 2x vk_adnetwork_reauth
     *      // refresh_token
     *      // client_credentials
     * < 2x class-vk-adnetwork-admin.redirect_after_admin_action_update
     *      // запрашиваем у МТ grant_type=client_credentials
     *      // удаление всех токенов (и в MT, и в WP)
     *
     * curl to  https://ads.vk.com/api/v2/oauth2/token.json
     * https://ads.vk.com/doc/api/ru/info/ApiAuthorization
     *
     * При достижении лимита на количество токенов можно самостоятельно удалить все токены конкретного пользователя. Для этого используется запрос вида:
     * POST /api/v2/oauth2/token/delete.json HTTP/1.1
     * Host: ads.vk.com
     * Content-Type: application/x-www-form-urlencoded
     * client_id={client_id}&client_secret={client_secret}&{username|user_id}={username|user_id}
     * где "username" – это логин пользователя, для которого необходимо удалить токены. Если параметр "username" не передан, то будут удалены токены аккаунта, для которого был выдан доступ к API.
     *
     */
    public static function vk_adnetwork_oauth2_token ($grant_type = '', $options = [] ) {
        if (!$options) $options = VK_Adnetwork::get_instance()->options();
        $options_vk_adnetwork_creds = $options['vk-adnetwork-creds'];
        $oauth2post = [
            'client_id'     => $options_vk_adnetwork_creds['client_id'],              // 'Y9hQeZBGOP8rPynf',
            'client_secret' => $options_vk_adnetwork_creds['client_secret'],          // 'iAWf33sTjCtVTRJEe8rj0iIRdOsSkjgOpxltW5myPMPE8TqjCJS2C2Js66rRF38LbJzNcEjReu62thWYJzvtN61tWqkxa4WgcI4J1tRIvNab71E3hgvMHW9aMcfNxWf8ds3yevQIk8WeKbHzY4NBbCTrIVKHRnQyfKNBfefcHdni3tDoftevVLaE1dZiZNdQzjGspjubUO491',
        ];
        $apiv2oauth2token = 'api/v2/oauth2/token';
        if (! $grant_type) { // = delete
            $url = VK_ADNETWORK_URL . "$apiv2oauth2token/delete.json";
        }else{ // client_credentials или refresh_token
            $url = VK_ADNETWORK_URL . "$apiv2oauth2token.json";
            $oauth2post['grant_type'] = $grant_type;
            if ($grant_type === 'refresh_token') $oauth2post['refresh_token'] = $options_vk_adnetwork_creds['refresh_token'];
        }
        $data = self::vk_adnetwork_curl($url, null, $oauth2post); // http_build_query() -x- Authorization: Bearer $token
        if (!isset($data['error'])) {
            if (! $grant_type) { // = delete
                unset($options['vk-adnetwork-creds']['access_token'], $options['vk-adnetwork-creds']['refresh_token'], $options['vk-adnetwork-creds']['delete_tokens']);
                unset($options['group_id'], $options['pad_id'], $options['slot_id'], $options['post_id']);
                // Токены удалены и сохранены
                $data['message'] = esc_html__('Tokens are deleted and saved', 'vk-adnetwork');
            }elseif (isset($data['access_token'])) { // client_credentials или refresh_token
                $options['vk-adnetwork-creds']['access_token']  = $data['access_token'];
                $options['vk-adnetwork-creds']['refresh_token'] = $data['refresh_token'];
                if ($grant_type === 'client_credentials') $options['vk-adnetwork-creds']['tokens_left'] = $data['tokens_left'] ?? '';
                // Токены обновлены и сохранены
                $data['message'] = esc_html__('Tokens are updated and saved', 'vk-adnetwork');
            }
            VK_Adnetwork::get_instance()->update_options($options); // update_option(VK_ADNETWORK_SLUG, $options);
            // echo "\n<!--vk_adnetwork_oauth2_token\n$url\n", http_build_query($oauth2post), "\n"; print_r($data); echo '-->';
        }else{
            // Токены НЕ обновлены, ошибка (vk_adnetwork_oauth2_token/$grant_type)
            $data['message'] = esc_html__('Tokens NOT updated, error', 'vk-adnetwork') . " (vk_adnetwork_oauth2_token/$grant_type)";
        }
        return $data;
        /*
            POST /api/v2/oauth2/token.json HTTP/1.1
            Host: ads.vk.com
            Content-Type: application/x-www-form-urlencoded
            grant_type=client_credentials&client_id={client_id}&client_secret={client_secret}
            grant_type=refresh_token&refresh_token={refresh_token}&client_id={client_id}&client_secret={client_secret}
            {
              "access_token": "DT4Pm52BShApFXWOsrDv9AhJ008wUz2g46VZcWN402Plr4j0rXDTzsd9Xphg4NfvsxoWOfoCAh4bw8Lfo7dmEkfebb0PTz8113CIGZmqMMjB6HKGL5K1igmCgcoBcld6UyeML4hnI9imeWCDi7VAvlpbeMcNZwUnOwsSoXGSn5QzrxiXto9aI5TFcQ1FGYTPCdg2QWOjugUHgnUVWsRtfzAboVDFwmsX2HvUZssKizxSQ7ScoQBAO5UPUvWA",
              "token_type": "Bearer",
              "expires_in": 86400,
              "scope": [],
              "refresh_token": "AlnCV8WtfB98PjkozZf7HIFAArPF1COAOR5cMlZ5Jside0mXc6bBDznG5SxfXaYZlytYfpLkgNb9xB6j1HCPPX7r5G5RloNMyTXNbvFbaNBS57U47l7CBS1dDKMKg5QC6essWnc5whWKLu6vJiQpDDMlr3u2wgPjxTeBrAJpZYgrKjM36SKvEuf0xwn3OHFRnkCfU1jEaaY1Ifkuk6I",
              "tokens_left": 3
            }
            or -- error + error_description
        */
    }

    public static function vk_adnetwork_curl ($url, $token = null, $post = null) {
        if (!$url) return null;
        $args = [];
//        $cURLConnection = curl-init();                                                              // 5 -x- vk_adnetwork_reauth($cURLConnection)
//        curl-setopt($cURLConnection, CURLOPT_URL, $url);                                            // 6 ALL                                                                    {{{{URL}}}}
        if ($token) {
            $args['headers'] = [ 'Authorization' => 'Bearer ' . $token ];
//            curl-setopt($cURLConnection, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);     // 5 -x- vk_adnetwork_oauth2_token                                                    {{{{TOKEN}}}}
        }
        if ($post) {
            // cURL error 28: Operation timed out after 5001 milliseconds with 0 bytes received
            // {"error":{"code":"validation_failed","message":"Validation failed","fields":{"message":"Bad JSON in the request","code":"bad_json"}}}
            $args['body'] = $post;
            $args['timeout'] = 60;
            $data = wp_remote_post($url, $args);
//            curl-setopt($cURLConnection, CURLOPT_POST, 1);                                          // 3 [ vk_adnetwork_group_pads_pads_post | vk_adnetwork_group_pads_post | vk_adnetwork_oauth2_token ]
//            curl-setopt($cURLConnection, CURLOPT_POSTFIELDS, $post);                                // 3 [ vk_adnetwork_group_pads_pads_post | vk_adnetwork_group_pads_post | vk_adnetwork_oauth2_token ]     {{{{POST}}}}
        }else{
            // {"error":{"code":"invalid_token","message":"Unknown access token"}}
            $data = wp_remote_get($url, $args);
        }
//        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);                                 // 5 -x- vk_adnetwork_reauth($cURLConnection)
//        $data = curl-exec($cURLConnection);                                                         // 7 ALL6+1
        if( is_object($data) ) { // WP_Error is_wp_error($data)
            return (array)$data;
        }
        $jdata = json_decode($data['body'], true);                                                    // 7 ALL6+1
//        curl-close($cURLConnection);                                                                // 5 -x- vk_adnetwork_reauth($cURLConnection)
        error_log(sanitize_url($_SERVER['REQUEST_URI'])
            . " [wp_remote_post|get]: $url | token: "
            . !!$token
            . ($post ? (' #post: ' . (is_string($post) ? strlen($post) : -count($post))) : ' 0> ' )   // . print_r($post, true)
            . ($data ? (' #data: ' . strlen($data['body'])) : ' <0 ') // . count($data, COUNT_RECURSIVE)
        );
        return $jdata;
    }

    /*
     * sanitize $array = [$key => $value, $key2 => $value2, .. ]
     * sanitize all values (recursively)
     * filtering array keys -- leaving only the keys from the list $keys = 'validKey1 validKey2 ...'
     * if we do not have a parameter $keys, we sanitize all the keys
     */
    public static function vk_adnetwork_sanitize_array ($array, $keys = '') {
        if ($keys) {
            $keys1 = array_flip(explode(' ', $keys));           // valid keys -- 'append_key append_text ad_id text closed' -> append_key=>1, append_text=>1, ad_id=>1, text=>1, closed=>1,
            $array = array_intersect_key($array, $keys1);       // оставим в $array только те ключи, которые выписаны в параметре $keys
            return array_map('sanitize_text_field', $array);    // sanitize_text_field() для всех значений
        }else{
            $sanitize_array = [];
            foreach ($array as $key => $value) {
                $sanitize_key = sanitize_text_field($key);
                if (is_array($value)) {
                    $sanitize_array[$sanitize_key] = self::vk_adnetwork_sanitize_array($value);
                }elseif (is_string($value)) {
                    $sanitize_array[$sanitize_key] = sanitize_text_field($value);
                }else {
                    $sanitize_array[$sanitize_key] = $value;
                }

            }
            return $sanitize_array;
        }
    }
}
