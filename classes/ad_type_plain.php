<?php

/**
 * VK AdNetwork Plain Ad Type
 *
 * @package   VK_Adnetwork
 * @license   GPL-2.0+
 * @link      https://vk.com
 * @copyright 2023 VK
 *
 * Class containing information about the plain text/code ad type
 *
 * see ad-type-content.php for a better sample on ad type
 */
class VK_Adnetwork_Ad_Type_Plain extends VK_Adnetwork_Ad_Type_Abstract {

    /**
     * ID - internal type of the ad type
     *
     * @var string $ID ad type id.
     */
    public $ID = 'plain';

    /**
     * Set basic attributes
     */
    public function __construct() {
        $this->title       = esc_html__( 'Plain Text and Code', 'vk-adnetwork' );
        $this->description = ''; // XZ что тут писать, мы не даём редактировать код, да и 'Plain Text and Code' -- тоже уже фуфло какое-то
        $this->parameters  = [
            'content' => '',
        ];
    }

    public function render_parameters ( &$ad ) {
        if ($ad->content) {
            echo wp_kses("<input type='hidden' name='vk_adnetwork[content]' id='vk_adnetwork-ad-content-plain' value='" . esc_attr($ad->content) . "'>",
                ['input' => ['type' => true, 'name' => true, 'id' => true, 'value' => true]]
            );
            return $ad->content;
        }
//        НИЖЕ ВСЁ ЗАКОММЕНТИРОВАНО -- только один новый блок format_id = 1524836 = inPage -- его код едет в хидден-поле
//          (всё остальное -- остатки, когда можно было выбирать разные ПАДЫ для рекламных мест ВП, и редактировать пады)
//        $pad_id = ''; // -X- $ad->content && preg_match('/data-pad-id="(\d+)"/', $ad->content, $match) ? $match[1] : '';
//        // -X- $pad_id = ($ad->content ?? '') ? trim(explode("\n", $ad->content, 2)[0], " <!->\r") : ''; // <!--$pad[id]-->\r\n ниже - первая строка кода МТ // <!--pad_id--> должен стоять прямо в начале контента!
//        $options = VK_Adnetwork::get_instance()->options();
//        $group_id = $options['group_id'] ?? '';
        // всё что ниже -- какое-то фуфло (на удаление?)
        // селекта выбора блока-МТ - НЕТ
        // размеры НЕ меняются
//        if (!$group_id) return '';
//        $group_pad = VK_Adnetwork_Utils::vk_adnetwork_group_pads($group_id); // все пады площадки -- будем делать select (выпадушку в редактировании объявления) из них
//        if (!$group_pad) return '';
//        $group = $group_pad['group_pads'][$group_id];
        // а в ней все пады // vk_adnetwork_width // vk_adnetwork_height
        $pads = [ // Создать новый блок в VK AdNetwork // classes/ad.php:525 save() {SLOT_ID} => $pad['slot_id']
            ['description' => '{DESCRIPTION}', 'id' => '{PAD_ID}', 'status' =>'', 'slot_id' => '{SLOT_ID}', 'format_id' => 1524836, // 109513,
               // 'style' => ['block__width' => '900px', 'block__height' => '250px', 'countEmbed__width' => '900', 'countEmbed__height' => '250']
            ], //
//            ...array_values($group['pads'] ?? [])
        ];
        // (!) \2/ вместо <select name='vk_adnetwork[content]'.. теперь <input type='hidden' name='vk_adnetwork[content]'..
        // echo "\n<select name='vk_adnetwork[content]' id='vk_adnetwork-ad-content-plain' style='width:400px;' onchange='wh2wh(this)'>\n";
        // $ad_ids = self::all_pdp_ads($pad_id, [ 'publish', 'draft', 'pending' ]); // будем дизейблить в селекте-выпадушке блоки-МТ, к которым привязаны рекламы (опубликованные, черновики, на-утверждении)
//        $f_WxH = [
//            109513 => [970, 250],
//            103888 => [300, 600],
//            103887 => [300, 250],
////            86883  => [240, 400],
//        ];
        foreach ($pads as $pad) {
            //if (isset($pad['style']['block__width']) && isset($pad['style']['block__height'])) {
            //      $width  = 'width: ' . $pad['style']['block__width'] . ';';
            //      $height = 'height: ' . $pad['style']['block__height'] . ';';
//            if (isset($pad['format_id']) && isset($f_WxH[$pad['format_id']])) {
//                $width  = 'width: '  . $f_WxH[$pad['format_id']][0] . 'px;';
//                $height = 'height: ' . $f_WxH[$pad['format_id']][1] . 'px;';
//                $wXh = "$width X $height:"; // это синхронно с JS: txt.match(/:(\d+)px X /) txt.match(/ X (\d+)px: /) ниже
//            }else{
//                list($width, $height, $wXh) = ['/*width*/', '/*height*/', ''];
//            }
            $content = ''; // "<!--$pad[id]-->\n<!---$pad[description]--->\n"; -X- <!--pad_id--> должен стоять прямо в начале контента! (и в отдельной строке!)
            $description = esc_attr($pad['description']);
            $content .= $pad['format_id'] == 1524836 // inPage
               ? " <div id=\"vk_adnetwork_inpage_slot_$pad[slot_id]\" data-pad-id=\"$pad[id]\" data-pad-description=\"$description\"></div> "
               : 'XZ';
//                "
//                <div style=\"text-align:center; width:100%;\"><ins class=\"mrg-tag\"
//                    style=\"display:inline-block;text-decoration:none; $width $height\"
//                    data-pad-id=\"$pad[id]\"
//                    data-pad-description=\"$description\"
//                    data-ad-client=\"ad-$pad[slot_id]\"
//                    data-ad-slot=\"$pad[slot_id]\"
//                ></ins></div>
//                <script> (MRGtag = window.MRGtag || []).push({}); </script>
//                ";
//            $selected = $pad_id == $pad['id'] ? 'selected' : '';                  // pad_id = мой муж (МТ-блок-пад этой объявы), он выбран в списке
            // (!) \5/ вместо <select name='vk_adnetwork[content]'.. теперь <input type='hidden' name='vk_adnetwork[content]'..
            // $other_ad_ids = $selected ? [$ad->id] : ($ad_ids[$pad['id']] ?? []);  // я ?: или другие жены МТ-блока
            // $disabled = !$selected && $other_ad_ids ? 'disabled="disabled"' : ''; // дизейблим блоки-МТ, у которых есть связи с рекламами (опубликованные, черновики, на-утверждении)
            // if ('' === $pad['id']) $other_ad_ids = ''; // у 'Создать новый блок в VK AdNetwork' не хотим указывать (ИД), хоть ИД у объявления УЖЕ есть, но тоже укажем (___)
            // $other_ad_ids = $other_ad_ids ? join(',', $other_ad_ids) : str_repeat('_', strlen($ad->id)); // (id1), (___) или (id1,id2) -- перед описанием МТ-блока -- его жена/ы или холостой
            // echo "<option value='$content' $selected $disabled>($other_ad_ids) pad #$pad[id]:$pad[status]:$wXh $pad[description]</option>\n"; // disabled if exist wordpress-ad with this block
//            if ($selected) { // размеры блока МТ могли изменить в другой рекламной позиции вордпресса
//                //$ad->width  = $pad['style']['countEmbed__width'];  // без px
//                //$ad->height = $pad['style']['countEmbed__height']; // без px
                $hidden = $content;
//            }
            // (!) вместо <select name='vk_adnetwork[content]'.. теперь <input type='hidden' name='vk_adnetwork[content]'..
            // if (!$pad['id']) echo "<option disabled>──────────</option>\n";
        }
        // (!) вместо <select name='vk_adnetwork[content]'.. теперь <input type='hidden' name='vk_adnetwork[content]'..
        // echo "\n</select>\n<br>";
        echo wp_kses("<input type='hidden' name='vk_adnetwork[content]' id='vk_adnetwork-ad-content-plain' value='" . esc_attr($hidden) ."'>",
            ['input' => ['type' => true, 'name' => true, 'id' => true, 'value' => true]]
        );
        return $hidden; // <br><textarea style='width: 60%; height: 185px;'>$hidden</textarea>
        //pad #1584249:active:900px X 250px: WP: otdyh-baikal.ru / 2023-04-12 15:34
        //pad #<PADID>:active:<W>px X <H>px: <DESCRIPTION = WP: host / Y-m-d H:i>
        /* (!) \11/ скрипт нужен для <select name='vk_adnetwork[content]' ..> -- а мы его закомментили!
         * echo '<script>
                function wh2wh(th) {
                   var txt = th.options[th.selectedIndex].text
                   var w = txt.match(/:(\d+)px X /)
                   var h = txt.match(/ X (\d+)px: /)
                   document.getElementById("vk_adnetwork_height").value = h && h.length ? h[1] : ""
                   var vk_adnetwork_width = document.getElementById("vk_adnetwork_width")
                   vk_adnetwork_width.value = w && w.length ? w[1] : ""
                   floatdisable(vk_adnetwork_width)
                }
        </script>';*/
    }

    /**
     * Prepare the ads frontend output
     *
     * @param VK_Adnetwork_Ad $ad ad object.
     *
     * @return string $content ad content prepared for frontend output.
     * @since 1.0.0
     */
    public function prepare_output( $ad ) {
        $content = $ad->content;

        if ( ! is_string( $content ) ) {
            return '';
        }

        /**
         * Apply do_blocks if the content has block code
         * works with WP 5.0.0 and later
         */
        if ( function_exists( 'has_blocks' ) && has_blocks( $content ) ) {
            $content = do_blocks( $content );
        }

        return (
            (
                ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) ||
                ! $this->author_can_unfiltered_html( (int) get_post_field( 'post_author', $ad->id ) )
            )
            && version_compare( $ad->options( 'last_save_version', '0' ), '1.35.0', 'ge' )
        )
            ? wp_kses( $content, wp_kses_allowed_html( 'post' ) )
            : $content;
    }

    /**
     * Check if the author of the ad can use unfiltered_html.
     *
     * @param int $author_id User ID of the ad author.
     *
     * @return bool
     */
    private function author_can_unfiltered_html( $author_id ) {
        if ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) {
            return false;
        }

        $unfiltered_allowed = user_can( $author_id, 'unfiltered_html' );
        if ( $unfiltered_allowed || ! is_multisite() ) {
            return $unfiltered_allowed;
        }

        $options = VK_Adnetwork::get_instance()->options();
        if ( ! isset( $options['allow-unfiltered-html'] ) ) {
            $options['allow-unfiltered-html'] = [];
        }
        $allowed_roles = $options['allow-unfiltered-html'];
        $user          = get_user_by( 'id', $author_id );

        return ! empty( array_intersect( $user->roles, $allowed_roles ) );
    }
}
