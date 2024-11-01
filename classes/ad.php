<?php
/**
 * VK AdNetwork Ad.
 *
 * @package   VK_Adnetwork_Ad
 * @license   GPL-2.0+
 * @link      https://vk.com
 * @copyright 2023 VK
 */

/**
 * An ad object
 *
 * @package VK_Adnetwork_Ad
 * @deprecated since version 1.5.3 (May 6th 2015)
 *  might still be needed if some old add-ons are running somewhere
 */
if ( ! class_exists( 'VK_AdnetworkAd', false ) ) {
    class VK_AdnetworkAd extends VK_Adnetwork_Ad {

    }
}

/**
 * An ad object
 *
 * @package VK_Adnetwork_Ad
 */
class VK_Adnetwork_Ad {

    /**
     * Id of the post type for this ad
     *
     * @var int $id
     */
    public $id = 0;

    /**
     * True, if this is an VK AdNetwork Ad post type
     *
     * @var bool $is_ad
     */
    public $is_ad = false;

    /**
     * Ad type
     *
     * @var string $type ad type.
     */
    public $type = 'content';

    /**
     * Notes about the ad usage
     *
     * @var string $description
     */
    public $description = '';

    /**
     * Ad width
     *
     * @var int $width width of the ad.
     */
    public $width = 0;

    /**
     * Target url
     *
     * @var string $url ad URL parameter.
     */
    public $url = '';

    /**
     * Ad height
     *
     * @var int $height height of the ad.
     */
    public $height = 0;

    /**
     * Object of current ad type
     *
     * @var VK_Adnetwork_Ad_Type_Abstract $type_obj object of the current ad type.
     */
    protected $type_obj;

    /**
     * Content of the ad
     *
     * Only needed for ad types using the post content field
     *
     * @var string $content content of the ad.
     */
    public $content = '';

    /**
     * Status of the ad (e.g. publish, pending)
     *
     * @var string $status status of the ad.
     */
    public $status = '';

    /**
     * Placement of the ad (e.g. post_top, post_content, post_bottom)
     *
     * @var string $placement_type placement of the ad.
     */
    public $placement_type = '';
    public $placement_p = '';

    /**
     * Pad id of the ad (in MytTarget)
     *
     * @var string $pad_id pad id of the ad.
     */
    public $pad_id = 0;

    /**
     * Slot id of the ad (in MytTarget)
     *
     * @var string $slot_id slot id of the ad.
     */
    public $slot_id = 0;

    /**
     * format_id of the ad (in MytTarget)
     *
     * @var string $format_id format id of the ad.
     */
    public int $format_id = 0;

    /**
     * Array with meta field options aka parameters
     *
     * @var array $options ad options.
     */
    protected $options;

    protected $old = [];

    /**
     * Name of the meta field to save options to
     *
     * @var string $options_meta_field under which post meta key the ad options are stored.
     */
    public static $options_meta_field = 'vk_adnetwork_ad_options';

    /**
     * Additional arguments set when ad is loaded, overwrites or extends options
     *
     * @var array $args
     */
    public $args = [];

    /**
     * Multidimensional array contains information about the wrapper
     * Each possible html attribute is an array with possible multiple elements
     *
     * @var array $wrapper options of the ad wrapper.
     */
    public $wrapper = [];

    /**
     * Will the ad be tracked?
     *
     * @var mixed $global_output
     */
    public $global_output;

    /**
     * Title of the ad
     *
     * @var string $title
     */
    public $title = '';

    /**
     * Displayed above the ad.
     *
     * @var string $label ad label.
     */
    protected $label = '';

    /**
     * Inline CSS object, one instance per ad.
     *
     * @var VK_Adnetwork_Inline_Css
     */
    private $inline_css;
    /**
     * Timestamp if ad has an expiration date.
     *
     * @var int
     */
    public $expiry_date = 0;

    /**
     * The ad expiration object.
     *
     * @var VK_Adnetwork_Ad_Expiration
     */
    private $ad_expiration;

    /**
     * The saved output options.
     *
     * @var array
     */
    public $output;

    /**
     * Whether the current ad is in a head placement.
     *
     * @var bool
     */
    public $is_head_placement;

    /**
     * Init ad object
     *
     * @param int   $id id of the ad.
     * @param array $args additional arguments.
     */
    public function __construct( $id, $args = [] ) {
        $this->id   = (int) $id;
        $this->args = is_array( $args ) ? $args : [];

        // whether the ad will be tracked.
        $this->global_output = !isset($this->args['global_output']) || (bool)$this->args['global_output'];

        // Run constructor to check early if ajax cache busting already created inline css.
        $this->inline_css = new VK_Adnetwork_Inline_Css();

        if ( ! empty( $this->id ) ) {
            $this->load( $this->id );
        }

    }

    /**
     * Load an ad object by id based on its ad type
     *
     * @param int $id ad id.
     *
     * @return bool false if ad could not be loaded.
     */
    private function load(int $id = 0) {

        $_data = get_post( $id );
        if ( null === $_data ) {
            return false;
        }

        // return, if not an ad.
        if ( VK_Adnetwork::POST_TYPE_SLUG !== $_data->post_type ) {
            return false;
        } else {
            $this->is_ad = true;
        }

        $this->type  = $this->options( 'type' );
        $this->title = $_data->post_title;
        /* load ad type object */
        $types = VK_Adnetwork::get_instance()->ad_types;
        if ( isset( $types[ $this->type ] ) ) {
            $this->type_obj = $types[ $this->type ];
        } else {
            $this->type_obj = new VK_Adnetwork_Ad_Type_Abstract();
        }


        $this->url                  = $this->get_url();
        $this->format_id            = absint($this->options('format_id', 1524836));
        $this->width                = absint($this->options('width', 0));
        $this->height               = absint($this->options('height', 0));
        $this->description          = $this->options('description');
        $this->placement_type       = $this->options('placement_type');
        $this->placement_p          = $this->options('placement_p');
        $this->pad_id               = $this->options('pad_id');
        $this->slot_id              = $this->options('slot_id');
        $this->output               = $this->options('output');
        $this->status               = $_data->post_status;
        $this->expiry_date          = (int)$this->options('expiry_date');
        $this->is_head_placement    = isset( $this->args['placement_type'] ) && 'header' === $this->args['placement_type'];
        $this->args['is_top_level'] = ! isset( $this->args['is_top_level'] );

        // load content based on ad type.
        $this->content = $this->type_obj->load_content( $_data );

        if ( ! $this->is_head_placement ) {
            $this->maybe_create_label();
            $this->wrapper = $this->load_wrapper_options();

            // set wrapper conditions.
            $this->wrapper = apply_filters( 'vk-adnetwork-set-wrapper', $this->wrapper, $this );
            // add unique wrapper id.
            if ( is_array( $this->wrapper )
                 && [] !== $this->wrapper
                 && ! isset( $this->wrapper['id'] ) ) {
                // create unique id if not yet given.
                $this->wrapper['id'] = $this->create_wrapper_id();
            }
        }

        $this->ad_expiration = new VK_Adnetwork_Ad_Expiration( $this );
        return true;
    }

    /**
     * Get options from meta field and return specific field
     *
     * @param string $field post meta key to be returned. Can be passed as array keys separated with `.`, i.e. 'parent.child' to retrieve multidimensional array values.
     * @param array  $default default options.
     *
     * @return mixed meta field content
     */
    public function options( $field = '', $default = null ) {
        // retrieve options, if not given yet
        if ( is_null( $this->options ) ) {
            // may return false.
            $meta = get_post_meta( $this->id, self::$options_meta_field, true );
            if ( $meta && is_array( $meta ) ) {
                // merge meta with arguments given on ad load.
                $this->options = VK_Adnetwork_Utils::vk_adnetwork_merge_deep_array( [ $meta, $this->args ] );
            } else {
                // load arguments given on ad load.
                $this->options = $this->args;
            }

            if ( isset( $this->options['change-ad'] ) ) {
                // some options was provided by the user.
                $this->options = VK_Adnetwork_Utils::vk_adnetwork_merge_deep_array(
                    [
                        $this->options,
                        $this->options['change-ad'],
                    ]
                );
            }
        }

        // return all options if no field given.
        if ( empty( $field ) ) {
            return $this->options;
        }

        $field = preg_replace( '/\s/', '', $field );
        $value = $this->options;
        foreach ( explode( '.', $field ) as $key ) {
            if ( ! isset( $value[ $key ] ) ) {
                $value = $default;
                break;
            }
            $value = $value[ $key ];
        }

        if ( is_null( $value ) ) {
            $value = $default;
        }

        /**
         * Filter the option value retrieved for $field.
         * `$field` parameter makes dynamic hook portion.
         *
         * @var mixed           $value The option value (may be set to default).
         * @var VK_Adnetwork_Ad $this  The current VK_Adnetwork_Ad instance.
         */
        return apply_filters( "vk-adnetwork-ad-option-$field", $value, $this );
    }

    /**
     * Set an option of the ad
     *
     * @param string $option name of the option.
     * @param mixed  $value value of the option.
     *
     * @since 1.1.0
     */
    public function set_option( $option = '', $value = '' ) {
        if ( '' === $option ) {
            return;
        }

        // get current options.
        $options = $this->options();

        // set options.
        $options[ $option ] = $value;

        // save options.
        $this->options = $options;

    }


    /**
     * Return ad content for frontend output
     *
     * @param array $output_options output options.
     *
     * @return string $output ad output
     * @since 1.0.0
     */
    public function output( $output_options = [] ) {
        if ( ! $this->is_ad ) {
            return '';
        }

        $this->global_output             = $output_options['global_output'] ?? $this->global_output;
        $output_options['global_output'] = $this->global_output;

        // switch between normal and debug mode.
        // check if debug output should only be displayed to admins.
        $user_can_manage_ads = current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_manage_options' ) );
        $debug4admin = $user_can_manage_ads || ( ! $user_can_manage_ads && ! defined( 'VK_ADNETWORK_AD_DEBUG_FOR_ADMIN_ONLY' ) );
        if ( $this->options( 'output.debugmode' ) && $debug4admin ) {
            $debug = new VK_Adnetwork_Ad_Debug();
            return $debug->prepare_debug_output( $this );
        } else {
            $output = $this->prepare_frontend_output();
            if ( $this->options( 'output.mtdebugmode' ) && $debug4admin ) {
                $output = str_replace('data-ad-query="via_plugin=1"', 'data-ad-query="via_plugin=1&test_mode=1"', $output); // &debug=1
            }
        }

        // add the ad to the global output array.
        $vk_adnetwork = VK_Adnetwork::get_instance();
        if ( $output_options['global_output'] ) {
            $new_ad = [
                'type'   => 'ad',
                'id'     => $this->id,
                'title'  => $this->title,
                'output' => $output,
            ];

            $vk_adnetwork->current_ads[] = $new_ad;
        }

        // action when output is created.
        do_action( 'vk-adnetwork-output', $this, $output, $output_options );

        return apply_filters( 'vk-adnetwork-output-final', $output, $this, $output_options );
    }

    /**
     * Check if the ad can be displayed in frontend due to its own conditions
     *
     * @param array $check_options check options.
     *
     * @return bool $can_display true if can be displayed in frontend
     * @since 1.0.0
     */
    public function can_display( $check_options = [] ) {
        $check_options = wp_parse_args(
            $check_options,
            [
                'passive_cache_busting' => false,
                'ignore_debugmode'      => false,
            ]
        );

        // prevent ad to show up through wp_head, if this is not a header placement.
        if ( doing_action( 'wp_head' ) && isset( $this->options['placement_type'] ) && 'header' !== $this->options['placement_type']
            && ! VK_Adnetwork_Compatibility::can_inject_during_wp_head() ) {
            return false;
        }

        // Check If the current ad is requested using a shortcode placed in the content of the current ad.
        if ( isset( $this->options['shortcode_ad_id'] ) && (int) $this->options['shortcode_ad_id'] === $this->id ) {
            return false;
        }

        // force ad display if debug mode is enabled.
        if ( isset( $this->output['debugmode'] ) && ! $check_options['ignore_debugmode'] ) {
            return true;
        }

        if ( ! $check_options['passive_cache_busting'] ) {
            // don’t display ads that are not published or private for users not logged in.
            if ( 'publish' !== $this->status && ! ( 'private' === $this->status && is_user_logged_in() ) ) {
                return false;
            }

        } elseif ( 'publish' !== $this->status ) {
            return false;
        }

        if ( $this->ad_expiration->is_ad_expired() ) {
            return false;
        }

        // add own conditions to flag output as possible or not.
        return apply_filters( 'vk-adnetwork-can-display', true, $this, $check_options );
    }

    /**
     * Save an ad to the database
     * takes values from the current state
     * вызывается И ПРИ выборе Выравнивание блока по вертикали! (тогда $this->content == "")
     */
    public function save() {
        global $wpdb;

        // remove slashes from content.
        $this->content = $this->prepare_content_to_save();

        // -X- <!--pad_id--> должен стоять прямо в начале контента!
        $newad = false;
        if ($this->content && str_contains($this->content, 'data-pad-id="{PAD_ID}"')) { // -X- strncmp("<!---->", $this->content, 7) === 0) { // у опции 'Создать новый блок в VK AdNetwork' такое начало -- и создадим
            $newad = true;
            // { -x- adaptive $this->width, $this->height } ->[id]  тут {{{ СОЗДАНИЕ НОВОГО ПАДА в МТ }}}
            $response_create_pad = VK_Adnetwork_Utils::vk_adnetwork_group_pads_pads_post($this->format_id, $this->title);
            if (!isset($response_create_pad['id'])) {
                echo wp_kses('<h3>Ошибка создания пада в МТ!</h3><pre>' . print_r($response_create_pad, true) . '</pre>', ['h3' => true, 'pre' => true]);
                return;
            }
            $options = VK_Adnetwork::get_instance()->options();
            $group_id = $options['group_id'];
            $group_pad = VK_Adnetwork_Utils::vk_adnetwork_group_pads($group_id); // все пады площадки -- ищем новый [id]
            $group = $group_pad['group_pads'][$group_id];
            foreach ($group['pads'] as $pad) {
                if ($pad['id'] == $response_create_pad['id']) { // наш новый пад! ширина и высота СОВПАДАЮТ! мы их и просили создать
                    $this->content = str_replace(             // if (isset($pad['block__width']) && isset($pad['block__height']))
                        // -X- <!--pad_id--> стоит в начале контента
                        // classes/ad_type_plain.php:72 render_parameters() 'slot_id' => '{SLOT_ID}'
                        [ '{PAD_ID}',    '{SLOT_ID}',              '{DESCRIPTION}'], // '<!---->',        '{BLOCK__WIDTH}',     '{BLOCK__HEIGHT}'
                        [$pad['id'], $pad['slot_id'], esc_attr($pad['description'])], // "<!--$pad[id]-->", $pad['block__width'], $pad['block__height']
                        $this->content
                    );
                    $this->pad_id = $pad['id'];
                    $this->slot_id = $pad['slot_id'];
                    $this->title = $pad['description'];
                    break;
                }
            }

        }

        // wp_update_post([ 'ID' => $this->id, 'post_content' => $this->content ]); // (!) nesting level of '256' : wp_update_post > VK_Adnetwork_Ad->save() > VK_Adnetwork_Admin_Ad_Type->save_ad(???) >> wp_update_post > ...
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->update( $wpdb->posts, [ 'post_content' => $this->content ], [ 'ID' => $this->id ] );

        // clean post from object cache.
        clean_post_cache( $this->id );

        // save other options to post meta field.
        $options = $this->options();

        $options['type'] = $this->type;
        $options['url']  = $this->url;
        // Inform the tracking add-on about the new url.
        unset( $options['tracking']['link'] );
        $options['width']          = $this->width;
        $options['height']         = $this->height;
        $options['format_id']      = $this->format_id;
        $options['placement_type'] = $this->placement_type;
        $options['pad_id']         = $this->pad_id;
        $options['slot_id']        = $this->slot_id;
        $options['placement_p']    = $this->placement_p;
        $options['expiry_date']    = $this->expiry_date;
        $options['description']    = $this->description;

        // save the plugin version, with every ad save.
        $options['last_save_version'] = VK_ADNETWORK_VERSION;


        // sanitize options before saving
        $options = $this->prepare_options_to_save( $options );

        // filter to manipulate options or add more to be saved.
        $options = apply_filters( 'vk-adnetwork-save-options', $options, $this );

        update_post_meta( $this->id, self::$options_meta_field, $options );
        if (
            $newad
            && $this->placement_type == 'sidebar_widget'
            // && $_POST['action'] == 'editpost'
            // \/ это очень вычурно -- $newad === <!----> в начале контента - надёжный симптом нового рекламного блока
            // && get_option( 'siteurl' ) . $_POST['_wp_http_referer'] == admin_url( 'post-new.php?post_type=vk_adnetwork' )
        ) {
            wp_redirect( admin_url('widgets.php') );
            exit;
        }
    }

    /**
     * Native filter for content field before being saved
     *
     * @return string $content ad content
     */
    public function prepare_content_to_save() {

        $content = $this->content;

        // load ad type specific parameter filter
        // @todo this is just a hotfix for type_obj not set, yet the cause is still unknown. Likely when the ad is first saved
        if ( is_object( $this->type_obj ) ) {
            $content = $this->type_obj->sanitize_content( $content );
        }

        // если юзер в полях [width] и [height] под селектом с выбором пада проставил -- то их надо просунуть в код МТ!
        // (если не нули/"" -- то width: {WIDTH}px; height: {HEIGHT}px;, если нули/"" -- то /*width*/ /*height*/)
        list($width, $height) = [$this->width, $this->height];
//        if (preg_match('/width: (\d+)px;/', $content, $match)) $this->old['width'] = $match[1];
//        if (preg_match('/height: (\d+)px;/', $content, $match)) $this->old['height'] = $match[1];
//        if (preg_match('/<!---(.+)--->/', $content, $match)) $this->old['title'] = $match[1];
        $content = preg_replace(['/width: \d+px;/',  '/\/\*width\*\//' ], $width  ? "width: {$width}px;"    : '/*width*/',  $content);
        $content = preg_replace(['/height: \d+px;/', '/\/\*height\*\//'], $height ? "height: {$height}px;"  : '/*height*/', $content);
        $content = preg_replace('/data-pad-description="[^"]*"/', 'data-pad-description="' . esc_attr($this->title) . '"', $content);

        // apply a custom filter by ad type.
        $content = apply_filters( 'vk-adnetwork-pre-ad-save-' . $this->type, $content );

        return $content;
    }

    /**
     * Sanitize ad options before being saved
     * allows some ad types to sanitize certain values
     *
     * @param array $options ad options.
     * @return array sanitized options.
     */
    public function prepare_options_to_save( $options ) {

        // load ad type specific sanitize function.
        // we need to load the ad type object if not set (e.g., when the ad is saved for the first time)
        if ( ! is_object( $this->type_obj ) || ! $this->type_obj->ID ) {
            $types = VK_Adnetwork::get_instance()->ad_types;
            if ( isset( $types[ $this->type ] ) ) {
                $this->type_obj = $types[ $this->type ];
            }
        }

        $options = $this->type_obj->sanitize_options( $options );

        return $options;
    }

    /**
     * Prepare ads output
     *
     * @return string.
     */
    public function prepare_frontend_output() {
        $options = $this->options();

        $output = $options['change-ad']['content'] ?? $this->type_obj->prepare_output($this);

        // don’t deliver anything, if main ad content is empty.
        if ( empty( $output ) ) {
            return '';
        }

        if ( ! $this->is_head_placement ) {
            // filter to manipulate the output before the wrapper is added
            $output = apply_filters( 'vk-adnetwork-output-inside-wrapper', $output, $this );

            // build wrapper around the ad.
            $output = $this->add_wrapper( $output );

            // add a clearfix, if set.
            if (
                ( ! empty( $this->args['is_top_level'] ) && ! empty( $this->args['placement_clearfix'] ) )
                || $this->options( 'output.clearfix' )
            ) {
                $output .= '<br style="clear: both; display: block; float: none;"/>';
            }
        }

        // apply a custom filter by ad type.
        $output = apply_filters( 'vk-adnetwork-ad-output', $output, $this );

        return $output;
    }

    /**
     * Load wrapper options set with the ad
     *
     * @return array $wrapper options array ready to be use in add_wrapper() function.
     * @since 1.3
     */
    protected function load_wrapper_options() {
        $wrapper = [];

        $position          = $this->options( 'output.position', '' );
        $use_placement_pos = false;

        if ( $this->args['is_top_level'] ) {
            if ( isset( $this->output['class'] ) && is_array( $this->output['class'] ) ) {
                $wrapper['class'] = $this->output['class'];
            }
            if ( ! empty( $this->args['placement_position'] ) ) {
                // If not group, Set placement position instead of ad position.
                $use_placement_pos = true;
                $position          = $this->args['placement_position'];
            }
        }

        switch ( $position ) {
            case 'left':
            case 'left_float':
            case 'left_nofloat':
                $wrapper['style']['float'] = 'left';
                break;
            case 'right':
            case 'right_float':
            case 'right_nofloat':
                $wrapper['style']['float'] = 'right';
                break;
            case 'center':
            case 'center_nofloat':
            case 'center_float':
                $wrapper['style']['margin-left']  = 'auto';
                $wrapper['style']['margin-right'] = 'auto';

                if (
                    ( ! $this->width || empty( $this->output['add_wrapper_sizes'] ) )
                    || $use_placement_pos
                ) {
                    $wrapper['style']['text-align'] = 'center';
                }

                // add css rule after wrapper to center the ad.
                break;
            case 'clearfix':
                $wrapper['style']['clear'] = 'both';
                break;
        }

        // add manual classes.
        if ( isset( $this->output['wrapper-class'] ) && '' !== $this->output['wrapper-class'] ) {
            $classes = explode( ' ', $this->output['wrapper-class'] );

            foreach ( $classes as $_class ) {
                $wrapper['class'][] = sanitize_text_field( $_class );
            }
        }

        if ( ! empty( $this->output['padding']['top'] ) ) {
            $wrapper['style']['padding-top'] = (int) $this->output['padding']['top'] . 'px';
        }
        if ( empty( $wrapper['style']['padding-right'] ) && ! empty( $this->output['padding']['right'] ) ) {
            $wrapper['style']['padding-right'] = (int) $this->output['padding']['right'] . 'px';
        }
        if ( ! empty( $this->output['padding']['bottom'] ) ) {
            $wrapper['style']['padding-bottom'] = (int) $this->output['padding']['bottom'] . 'px';
        }
        if ( empty( $wrapper['style']['padding-left'] ) && ! empty( $this->output['padding']['left'] ) ) {
            $wrapper['style']['padding-left'] = (int) $this->output['padding']['left'] . 'px';
        }

        if ( ! empty( $this->output['add_wrapper_sizes'] ) ) {
            if ( ! empty( $this->width ) ) {
                $wrapper['style']['width'] = $this->width . 'px';
            }
            if ( ! empty( $this->height ) ) {
                $wrapper['style']['height'] = $this->height . 'px';
            }
        }

        if ( ! empty( $this->output['clearfix_before'] ) ) {
            $wrapper['style']['clear'] = 'both';
        }

        return $wrapper;
    }

    /**
     * Add a wrapper arount the ad content if wrapper information are given
     *
     * @param string $ad_content content of the ad.
     *
     * @return string $wrapper ad within the wrapper
     * @since 1.1.4
     */
    protected function add_wrapper( $ad_content = '' ) {
        $wrapper_options = apply_filters( 'vk-adnetwork-output-wrapper-options', $this->wrapper, $this );

        if ( $this->label && ! empty( $wrapper_options['style']['height'] ) ) {
            // Create another wrapper so that the label does not reduce the height of the ad wrapper.
            $height = [ 'style' => [ 'height' => $wrapper_options['style']['height'] ] ];
            unset( $wrapper_options['style']['height'] );
            $ad_content = '<div' . VK_Adnetwork_Utils::vk_adnetwork_build_html_attributes( $height ) . '>' . $ad_content . '</div>';
        }

        // Adds inline css to the wrapper.
        if ( ! empty( $this->options['inline-css'] ) && $this->args['is_top_level'] ) {
            $wrapper_options = $this->inline_css->add_css( $wrapper_options, $this->options['inline-css'], $this->global_output );
        }

        if (
            ! defined( 'VK_ADNETWORK_DISABLE_EDIT_BAR' )
            // Add edit button for users with the appropriate rights.
            && current_user_can( VK_Adnetwork_Plugin::user_cap( 'vk_adnetwork_edit_ads' ) )
            // We need a wrapper. Check if at least the placement wrapper exists.
            && ! empty( $this->args['placement_type'] )
        ) {
            ob_start();
            include VK_ADNETWORK_BASE_PATH . 'public/views/ad-edit-bar.php';
            $ad_content = trim( ob_get_clean() ) . $ad_content;
        }

        if ( ( ! isset( $this->output['wrapper-id'] ) || '' === $this->output['wrapper-id'] )
             && [] === $wrapper_options || ! is_array( $wrapper_options ) ) {
            return $this->label . $ad_content;
        }

        // create unique id if not yet given.
        if ( empty( $wrapper_options['id'] ) ) {
            $wrapper_options['id'] = $this->create_wrapper_id();
            $this->wrapper['id']   = $wrapper_options['id'];
        }

        $wrapper_element = ! empty( $this->args['inline_wrapper_element'] ) ? 'span' : 'div';

        // build the box
        $wrapper = '<' . $wrapper_element . VK_Adnetwork_Utils::vk_adnetwork_build_html_attributes( array_merge(
            $wrapper_options,
                $this->output['wrapper_attrs'] ?? []
        ) ) . '>';
        $wrapper .= $this->label;
        $wrapper .= apply_filters( 'vk-adnetwork-output-wrapper-before-content', '', $this );
        $wrapper .= $ad_content;
        $wrapper .= apply_filters( 'vk-adnetwork-output-wrapper-after-content', '', $this );
        $wrapper .= '</' . $wrapper_element . '>';

        return $wrapper;
    }

    /**
     * Create a random wrapper id
     *
     * @return string $id random id string
     * @since 1.1.4
     */
    private function create_wrapper_id() {

        if ( isset( $this->output['wrapper-id'] ) ) {
            $id = sanitize_key( $this->output['wrapper-id'] );
            if ( '' !== $id ) {
                return $id;
            }
        }

        $prefix = VK_Adnetwork_Plugin::get_instance()->get_frontend_prefix();

        return $prefix . wp_rand();
    }

    /**
     * Create an "Advertisement" label if conditions are met.
     */
    public function maybe_create_label() {
        $placement_state = $this->args['ad_label'] ?? 'default';

        $label = VK_Adnetwork::get_instance()->get_label( $placement_state );

        if ( $this->args['is_top_level'] && $label ) {
            $this->label = $label;
        }
    }

    /**
     * Get the ad url.
     *
     * @return string
     */
    private function get_url() {
        $this->url = $this->options( 'url' );

            global $pagenow;
            // If this is not the ad edit page.
            if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow && $this->url) {
                // Remove placeholders.
                $this->url = str_replace(
                    [
                        '[POST_ID]',
                        '[POST_SLUG]',
                        '[CAT_SLUG]',
                        '[AD_ID]',
                    ],
                    '',
                    $this->url
                );
            }

        return $this->url;
    }

}
