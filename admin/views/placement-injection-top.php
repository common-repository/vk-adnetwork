<?php
defined( 'ABSPATH' ) || exit;

/**
 * Render placements after publishing an ad.
 *
 * #var array $placements array with placements.
 *
 * @var array $select выбранное место -- его обводим (checked)
 * @var $ad -- реклама (VK_Adnetwork_Ad) со всеми своими причиндалами, в т.ч. с $ad->format_id
 *
 * // $( '#vk_adnetwork-ad-injection-box .vk_adnetwork-loader' ).show()
 * // $( '#vk_adnetwork-ad-injection-box-placements' ).hide()
 * >> $( 'body' ).animate( { scrollTop: $( '#vk_adnetwork-ad-injection-box' ).offset().top - 40 }, 1, 'linear' )
 */
 ?>
<div id="vk_adnetwork-ad-injection-box" class="vk_adnetwork-ad-metabox "><!--postbox-->
  <div id="vk_adnetwork-ad-injection-box-placements">
      <div class="vk_adnetwork-ad-injection-box-options">
        <label class="vk_adnetwork-ad-injection-box-option">
          <input
            <?php echo wp_kses($ad->format_id == 1524836 ? '' : 'disabled="disabled"', []); // ВВЕРХУ (inPage) ?>
            type="radio"
            required
            name="vk_adnetwork[placement_type]"
            data-placement-type="post_top"
            id="vk_adnetwork-ad-injection-button-post_top"
            value="post_top"
            class="vk_adnetwork-ad-injection-box-option-input"
            <?php echo esc_attr($select['post_top'] ?? false ? 'checked' : ''); ?>
            <?php echo wp_kses($select['post_top'] ?? '', []); ?>
          />
          <div
            id="vk_adnetwork-ad-injection-button-post_top-icon"
            class="vk_adnetwork-ad-injection-box-option-icon"
            style="background-image: url(<?php echo esc_url( VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/content-before.svg' ); ?>);
                        <?php echo esc_attr($ad->format_id == 1524836 ? '' : 'opacity:0.3;') // inPage ?>
                  "
          >
            <div class="vk_adnetwork-ad-injection-box-option-hint">
            <?php esc_html_e( 'Top section', 'vk-adnetwork' ); ?>
            </div>
          </div>
        </label>
        <label class="vk_adnetwork-ad-injection-box-option">
          <input
            <?php echo wp_kses($ad->format_id == 1524836 ? '' : 'disabled="disabled"', []); // ВСЕРЁДКЕ (inPage) ?>
            type="radio"
            required
            name="vk_adnetwork[placement_type]"
            data-placement-type="post_content"
            id="vk_adnetwork-ad-injection-button-post_content"
            class="vk_adnetwork-ad-injection-box-option-input"
            value="post_content"
            <?php echo esc_attr($select['post_content'] ?? false ? 'checked' : ''); ?>
            <?php echo wp_kses($select['post_content'] ?? '', []); ?>
          />
          <div
                  id="vk_adnetwork-ad-injection-button-post_content-icon"
                  class="vk_adnetwork-ad-injection-box-option-icon"
            style="background-image: url(<?php echo esc_url( VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/content-within.svg' ); ?>);
                        <?php echo esc_attr($ad->format_id == 1524836 ? '' : 'opacity:0.3;') // inPage ?>
                  "
          >
            <div class="vk_adnetwork-ad-injection-box-option-hint">
            <?php esc_html_e( 'In page content', 'vk-adnetwork' ); ?>
            </div>
          </div>
        </label>
        <label class="vk_adnetwork-ad-injection-box-option">
          <input
            <?php echo wp_kses($ad->format_id == 1524836 ? '' : 'disabled="disabled"', []); // ВНИЗУ (inPage) ?>
            type="radio"
            required
            name="vk_adnetwork[placement_type]"
            data-placement-type="post_bottom"
            id="vk_adnetwork-ad-injection-button-post_bottom"
            class="vk_adnetwork-ad-injection-box-option-input"
            value="post_bottom"
            <?php echo esc_attr($select['post_bottom'] ?? false ? 'checked' : ''); ?>
            <?php echo wp_kses($select['post_bottom'] ?? '', []); ?>
          />
          <div
                  id="vk_adnetwork-ad-injection-button-post_bottom-icon"
                  class="vk_adnetwork-ad-injection-box-option-icon"
            style="background-image: url(<?php echo esc_url( VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/content-after.svg' ); ?>);
                        <?php echo esc_attr($ad->format_id == 1524836 ? '' : 'opacity:0.3;') // inPage ?>
                  "
          >
            <div class="vk_adnetwork-ad-injection-box-option-hint">
            <?php esc_html_e( 'Bottom section', 'vk-adnetwork' ); ?>
            </div>
          </div>
        </label>
        <label class="vk_adnetwork-ad-injection-box-option">
          <input
              <?php echo wp_kses($ad->format_id == 109513 ? '' : 'disabled="disabled"', []); // ФУТЕР (970x250) ?>
              type="radio"
              required
              name="vk_adnetwork[placement_type]"
              data-placement-type="footer"
              id="vk_adnetwork-ad-injection-button-footer"
              class="vk_adnetwork-ad-injection-box-option-input"
              value="footer"
              <?php echo esc_attr($select['footer'] ?? false ? 'checked' : ''); ?>
              <?php echo wp_kses($select['footer'] ?? '', []); ?>
          />
          <div
                  id="vk_adnetwork-ad-injection-button-footer-icon"
                  class="vk_adnetwork-ad-injection-box-option-icon"
                  style="background-image: url(<?php echo esc_url( VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/footer.png' ); ?>);
                            <?php echo esc_attr($ad->format_id == 109513 ? '' : 'opacity:0.3;') // 970x250 ?>
                        "
          >
              <div class="vk_adnetwork-ad-injection-box-option-hint">
                  <?php esc_html_e( 'Footer', 'vk-adnetwork' ); ?>
              </div>
          </div>
        </label>
        <label class="vk_adnetwork-ad-injection-box-option">
          <input
              <?php $widgets = current_theme_supports( 'widgets' ); ?>
              <?php echo wp_kses(($ad->format_id == 103887 || $ad->format_id == 103888) && $widgets ? '' : 'disabled="disabled"', []); // ВИДЖЕТ-САЙДБАР (300x600, 300x250) ?>
              type="radio"
              required
              name="vk_adnetwork[placement_type]"
              data-placement-type="sidebar_widget"
              id="vk_adnetwork-ad-injection-button-sidebar_widget"
              class="vk_adnetwork-ad-injection-box-option-input"
              value="sidebar_widget"
              <?php echo esc_attr($select['sidebar_widget'] ?? false ? 'checked' : ''); ?>
              <?php echo wp_kses($select['sidebar_widget'] ?? '', []); ?>
          />
          <div
                  id="vk_adnetwork-ad-injection-button-sidebar_widget-icon"
                  class="vk_adnetwork-ad-injection-box-option-icon"
                  style="background-image: url(<?php echo esc_url( VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/widget.png' ); ?>);
                            <?php echo esc_attr(($ad->format_id == 103887 || $ad->format_id == 103888) && $widgets ? '' : 'opacity:0.3;') // ВИДЖЕТ-САЙДБАР (300x600, 300x250) ?>
                        "
          >
              <div class="vk_adnetwork-ad-injection-box-option-hint">
                  <?php // Виджет боковой панели
                  esc_html_e( 'Sidebar Widget', 'vk-adnetwork' );
                  ?>
              </div>
          </div>
        </label>
        <label class="vk_adnetwork-ad-injection-box-option">
          <input
            type="radio"
            required
            name="vk_adnetwork[placement_type]"
            data-placement-type="default"
            id="vk_adnetwork-ad-injection-button-default"
            class="vk_adnetwork-ad-injection-box-option-input"
            value="default"
            <?php echo esc_attr($select['default'] ?? false ? 'checked' : ''); ?>
            <?php echo wp_kses($select['default'] ?? '', []); ?>
          />
          <div
            class="vk_adnetwork-ad-injection-box-option-icon"
            style="background-image: url(<?php echo esc_url( VK_ADNETWORK_BASE_URL . 'admin/assets/img/placements/manual.svg' ); ?>)"
          >
            <div class="vk_adnetwork-ad-injection-box-option-hint">
              <?php esc_html_e( 'PHP or Shortcode', 'vk-adnetwork' ); ?>
            </div>
          </div>
        </label>

        <span class="vk_adnetwork-loader" style="display: none;"></span>
      </div>

        <?php /* if ( !isset($select['default']) ) { ?>
            <script>
                // DL: только когда выбрано местоположение default (АКА ручная вставка кода/шортката)
                //    показываем блоки с кодом/шорткатом (в других вариантах расположения -- прячем)
                // ++ /admin/assets/js/admin.js:61 ~~ $( '#vk_adnetwork-ad-injection-box .vk_adnetwork-ad-injection-button' ).on( 'click', ...
                jQuery( document ).ready( function ( $ ) {
                    $( '#vk_adnetwork-usage-shortcode-group' ).hide() // прячем див Шорткод ...
                    $( '#vk_adnetwork-usage-function-group' ).hide()  // прячем див Шаблон (PHP) ...
                    $( '.vk_adnetwork-usage' ).hide()                 // прячем инпуты [vk_adnetwork_the_ad id="..."] и < ?php vk_adnetwork_the_ad('...'); ? >
                })
            </script>
        <?php } */ ?>

  </div>
  <?php /*
 <div id="vk_adnetwork-ad-injection-message-placement-created" class="hidden">
  <p><?php
        // Поздравляем! Ваше объявление теперь видно во фронтенде.
        esc_html_e( 'Congratulations! Your ad is now visible in the frontend.', 'vk-adnetwork' );
    ?></p>
  <p>
  <?php
  printf(
    wp_kses(
    // translators: %s is a URL.

      __( 'Ad not showing up? Take a look <a href="%s" target="_blank">here</a>', 'vk-adnetwork' ),
      [
        'a' => [
          'href'   => [],
          'target' => [],
        ],
      ]
    ),
        esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#ads-not-showing-up' ) )
  );
  ?>
    </p>
  </div> */ ?>
</div>
  <?php
