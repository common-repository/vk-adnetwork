<?php
defined( 'ABSPATH' ) || exit;

/**
 *
 * 1. admin/includes/class-meta-box.php = VK_Adnetwork_Admin_Meta_Boxes > markup_meta_boxes() > admin/views/ad-parameters-metabox.php
 * 2. classes/ad_ajax_callbacks.php = VK_Adnetwork_Ad_Ajax_Callbacks > vk_adnetwork_load_ad_parameters_metabox()
 *
 * @var $ad
 * @var $adcode
 */
$nowidgets = current_theme_supports( 'widgets' ) ? '' : ' disabled';
$formats = [
    1524836 => ['inPage',  ''],
     109513 => ['970x250', ''],
     103888 => ['300x600', $nowidgets],
     103887 => ['300x250', $nowidgets],
//      86883 => '240x400',
];
add_filter( 'safe_style_css', function( $styles ) { $styles[] = 'opacity'; return $styles; } );

?>
<?php wp_nonce_field( 'vk_adnetwork_ad_post_form', 'vk_adnetwork_ad_post_form_nonce' ); ?>
<div id="vk-adnetwork-ad-parameters-size" class="vk_adnetwork-ad-metabox ">

    <div id="vk-ads-ad-parameters-size" class="vk_adnetwork-ad-injection-box-options">
        <?php /*<label> <select name="vk_adnetwork[format_id]"  id="vk_adnetwork_format_id" <?php echo $ad->content ? 'disabled' : '' ?> onchange="floatdisable(this)"> */ ?>
        <?php 
        foreach ( $formats as $format_id => $f ) {
            // echo '<option value="' . esc_attr($format_id) . '"' . selected($ad->format_id, $format_id, false) . '>' . $f . '</option>';
            echo wp_kses('<label class="vk_adnetwork-ad-injection-box-option">'
                . '<input class="vk_adnetwork-ad-injection-box-option-input" onclick="floatdisable(this)" required type="radio" name="vk_adnetwork[format_id]" value="' . esc_attr($format_id) . '" '
                .   checked($ad->format_id, $format_id, false)
                .   ( $ad->content ? ' disabled="disabled"' : $f[1] ) // 300x600, 300x250 disabled if not current_theme_supports(widgets)
                . '/>'
                . '<div class="vk_adnetwork-ad-injection-box-option-icon" style="background-image: url('
                .   esc_url( VK_ADNETWORK_BASE_URL . 'admin/assets/img/format-vkui/' . $format_id . '.svg' )
                . ');'
                . ( ($ad->content && $ad->format_id != $format_id) || $f[1] ? 'opacity:0.3;' : '' )
                . '">'
                .   '<div class="vk_adnetwork-ad-injection-box-option-hint">'
                .       esc_html( $f[0] )
                .   '</div>'
                . '</div>'
                . '</label>',
                [
                    'label' => ['class' => true],
                    'input' => ['class' => true, 'onclick' => true, 'required' => true, 'type' => true, 'name' => true, 'value' => true, 'checked' => true, 'disabled' => true],
                    'div' => ['class' => true, 'style' => true],
                ]
            );
        }
        ?>
        <?php
            if($nowidgets) {
                // Форматы «300x600» и «300x250» и расположение «Виджет боковой панели»<br>
                echo wp_kses( __('The formats "300x600" and "300x250" and the location of the "Sidebar widget"<br>', 'vk-adnetwork'), ['br' => true]);
                // доступны только в темах, которые поддерживают виджеты (сайдбар).<br>
                echo wp_kses( __('available only in themes that support widgets (sidebar).<br>', 'vk-adnetwork'), ['br' => true]);
                // Вы используете тему, которая не поддерживает виджеты.
                esc_html_e('You are using a theme that does not support widgets.', 'vk-adnetwork');
            }
            /* </select> */
        ?>
        <input type="hidden" value="<?php echo isset( $ad->width )  ? esc_attr( $ad->width )  : 970; ?>" name="vk_adnetwork[width]"  id="vk_adnetwork_width">
        <input type="hidden" value="<?php echo isset( $ad->height ) ? esc_attr( $ad->height ) : 250; ?>" name="vk_adnetwork[height]" id="vk_adnetwork_height">
    </div>

    <?php if ($ad->pad_id && $ad->slot_id) { ?>
        <div class="description">
            <?php
            echo wp_kses( // Блок в VK AdNetwork
                    esc_html__('Block in VK AdNetwork', 'vk-adnetwork')
                    . ' &mdash; <u name="' . VK_ADNETWORK_URL . 'pads/' . absint($ad->pad_id) . '/edit">' . absint($ad->slot_id) . '</a>'
                    . '</div><div class="description">'
                    // Рекламный код этого блока, который будет вставлен
                    . esc_html__('The advertising code of this block to be inserted', 'vk-adnetwork')
                    . ' &mdash; ',
                ['a' => ['href' => true], 'u' => ['name' => true], 'div' => ['class' => true]]
            );
            ?>
            <span class="vk_adnetwork-help">
          <span class="vk_adnetwork-tooltip">
            <?php echo wp_kses('<code>' . esc_html($adcode) . '</code>', ['code' => true]); ?>
          </span>
        </span>
        </div>
    <?php } ?>

    <div id="vk_adnetwork-description-for-widgets-php" class="description" style="display: <?php echo esc_html(in_array($ad->format_id, [103887, 103888] ) && !$nowidgets ? 'block' : 'none') ?>;">
        <span id="vk_adnetwork-description-for-widgets-php-span" style="display: none;"><?php
            // этот текст в начале всегда скрыт -- он может появиться у НОВОГО рекламного блока (т.е. у него радиобатоны выбора формата не дизейбл)
            // и при выборе САЙДБАРНОГО формата [103887, 103888] -- onclick="floatdisable(this)"
            // Когда вы нажмёте кнопку <u>Сохранить</u> или <u>Опубликовать</u> вы попадете в
            echo wp_kses( __('When you press the button <u>Save</u> or <u>Publish</u> you will be taken to ', 'vk-adnetwork'), ['u' => true]);
        ?></span>
        <?php
        printf( // <a href="%s">Редактор виджетов вордпресса</a>, в нем можно вставить этот рекламный блок в виджет-сайдбар.
            // translators: %s is the address of the WordPress widget editor
            wp_kses(__('<a href="%s">The WordPress widget editor</a>, where you can insert this ad block into the sidebar widget.', 'vk-adnetwork'), ['a' => ['href' => true]]),
            esc_url(admin_url('widgets.php'))
        );
        ?>
    </div>


</div>


<script>
  // DL: это жуткое поделие делает вот что:
  // когда ширина объявления больше 500 -- то обтекание его текстом выглядит страшненько (часто (ХЗ почему) баннер накладывается на обтекаемый текст и т.п.)
  // ну и вообще обтекание такого широкого баннера -- выглядит не айс!
  // поэтому я, если ширина больше 500:
  // (А) скрываю две кнопки для вставки с обтеканием (left_float и right_float)
  // (Б) дизейблю их, (В) убираю с них галочку "выбрано", (Г) пишу сообщение, что ширина больше 500 и нахспляжа
  // и наоборот -- если ширина меньше 500: (А) открываю обе кнопки, (Б) раздизейблю, (Г) убираю сообщение
  function floatdisable(th) {
    var vk_adnetwork_width  = document.getElementById('vk_adnetwork_width')
    var vk_adnetwork_height = document.getElementById('vk_adnetwork_height')
    switch(th.value) {
      case '1524836': // XZ надо ли это? ))
          vk_adnetwork_width.value = 0
          vk_adnetwork_height.value = 0
          break;
      case '109513':
          vk_adnetwork_width.value = 970
          vk_adnetwork_height.value = 250
          break;
      case '103888':
          vk_adnetwork_width.value = 300
          vk_adnetwork_height.value = 600
          break;
      case '103887':
          vk_adnetwork_width.value = 300
          vk_adnetwork_height.value = 250
          break;
      // case '86883':
      //     vk_adnetwork_width.value = 240
      //     vk_adnetwork_height.value = 400
      //     break;
    }
    var i_inpage = th.value == 1524836 // => 'inPage'
    var i_footer = th.value ==  109513 // => 'footer'

    var post_top        = document.getElementById("vk_adnetwork-ad-injection-button-post_top")
    var post_content    = document.getElementById("vk_adnetwork-ad-injection-button-post_content")
    var post_bottom     = document.getElementById("vk_adnetwork-ad-injection-button-post_bottom")
    var sidebar_widget  = document.getElementById("vk_adnetwork-ad-injection-button-sidebar_widget")
    var footer          = document.getElementById("vk_adnetwork-ad-injection-button-footer")

    var icon_post_top       = document.getElementById("vk_adnetwork-ad-injection-button-post_top-icon")
    var icon_post_content   = document.getElementById("vk_adnetwork-ad-injection-button-post_content-icon")
    var icon_post_bottom    = document.getElementById("vk_adnetwork-ad-injection-button-post_bottom-icon")
    var icon_sidebar_widget = document.getElementById("vk_adnetwork-ad-injection-button-sidebar_widget-icon")
    var icon_footer         = document.getElementById("vk_adnetwork-ad-injection-button-footer-icon")


    post_top.disabled       = ! i_inpage
    post_content.disabled   = ! i_inpage
    post_bottom.disabled    = ! i_inpage
    footer.disabled         = ! i_footer
    sidebar_widget.disabled = i_inpage || i_footer

    icon_post_top.style.opacity       = i_inpage ? 1 : 0.3
    icon_post_content.style.opacity   = i_inpage ? 1 : 0.3
    icon_post_bottom.style.opacity    = i_inpage ? 1 : 0.3
    icon_footer.style.opacity         = i_footer ? 1 : 0.3
    icon_sidebar_widget.style.opacity = i_inpage || i_footer ? 0.3 : 1

    if (i_inpage) {
        footer.checked = false
        sidebar_widget.checked = false
    }else{
        post_top.checked = false
        post_content.checked = false
        post_bottom.checked = false
        if (i_footer) {
            sidebar_widget.checked = false
        }else{
            footer.checked = false
        }
    }

    document.getElementById("vk_adnetwork-ad-content-plain").value = i_inpage
        ? '<div id="vk_adnetwork_inpage_slot_{SLOT_ID}" data-pad-id="{PAD_ID}" data-pad-description="{DESCRIPTION}"></div>'
        : '<div style="text-align:center; width:100%;"><ins class="mrg-tag" data-ad-query="via_plugin=1" data-pad-id="{PAD_ID}" data-pad-description="{DESCRIPTION}" style="display:inline-block; text-decoration:none; width: ' + vk_adnetwork_width.value + 'px; height: ' + vk_adnetwork_height.value + 'px;" data-ad-client="ad-{SLOT_ID}" data-ad-slot="{SLOT_ID}"></ins></div><script>(MRGtag = window.MRGtag || []).push({});</scr'+'ipt>'
/* -X- <!---->\n<!------>\n , <!---->\n<!------>\n */
      // эта функция работает только у НОВЫХ рекламных блоков (у СТАРЫХ рабиодатоны задизейблены)
      // так что показываем (Редактор виджетов вордпресса, в нем можно вставить этот рекламный блок в виджет-сайдбар) -- когда не инпейдж и не футер (т.е. сайдбарные два формата)
      document.getElementById("vk_adnetwork-description-for-widgets-php").style.display = i_inpage || i_footer ? 'none' : 'block'
      // а текст (Когда вы нажмёте кнопку Сохранить или Опубликовать вы попадете в ) только если не инпейдж и не футер (т.е. сайдбарные два формата) и не выбрано расположение шорткод
      var shortcode = document.getElementById('vk_adnetwork-ad-injection-button-default').checked
      document.getElementById("vk_adnetwork-description-for-widgets-php-span").style.display = i_inpage || i_footer || shortcode ? 'none' : 'block'

  }
</script>
