<?php
defined( 'ABSPATH' ) || exit;

/**
 * Render Layout/Output meta box on ad edit screen.
 *
 * @var bool   $has_position       true if the position option is set.
 * @var bool   $has_clearfix       true if the clearfix option is enabled.
 * @var string $margin             value for margin option.
 * @var string $wrapper_id         value for wrapper ID option.
 * @var bool   $debug_mode_enabled true if the ad debug mode option is enabled.
 * @var bool   $mtdebug_mode_enabled true if the ad debug mode option is enabled.
 * @var string $positioning        The positioning view.
 * @var  $ad
 */
?>

<input type="hidden" value="5" name="vk_adnetwork[output][padding][top]">
<input type="hidden" value="5" name="vk_adnetwork[output][padding][left]">
<input type="hidden" value="5" name="vk_adnetwork[output][padding][right]">
<input type="hidden" value="5" name="vk_adnetwork[output][padding][bottom]">

<div class="vk_adnetwork-option-list">

  <!-- <hr class="vk_adnetwork-hide-in-wizard"/> -->
  <div class="field">
  <label for="vk_adnetwork-output-debugmode" class="vk_adnetwork-hide-in-wizard checkbox">
    <input id="vk_adnetwork-output-debugmode" type="checkbox" name="vk_adnetwork[output][debugmode]" value="1"
        <?php checked( $debug_mode_enabled, true ); // DL: onchange вырубает другой дебаг (VK AdNetwork) ?>
        onchange="var mtd = document.getElementById('vk_adnetwork-output-mtdebugmode'); if (this.checked && mtd.checked) mtd.checked = false"
    />
    <?php esc_html_e( 'Enable debug mode', 'vk-adnetwork' ); ?> (WordPress)
  </label>
  </div>
        <!-- <a href="<?php echo esc_url( admin_url( 'admin.php?page=vk-adnetwork-support#wp-debug' ) ); ?>" target="_blank" class="vk_adnetwork-manual-link"><?php esc_html_e( 'Manual', 'vk-adnetwork' ); ?></a> -->
    <!-- <hr class="vk_adnetwork-hide-in-wizard"/> -->
    <div class="field">
    <label for="vk_adnetwork-output-mtdebugmode" class="vk_adnetwork-hide-in-wizard checkbox">
      <input id="vk_adnetwork-output-mtdebugmode" type="checkbox" name="vk_adnetwork[output][mtdebugmode]" value="1"
          <?php checked( $mtdebug_mode_enabled, true ); // DL: onchange вырубает другой дебаг (wordpress) ?>
          onchange="var wpd = document.getElementById('vk_adnetwork-output-debugmode'); if (this.checked && wpd.checked) wpd.checked = false"
      />
      <?php esc_html_e( 'Enable debug mode', 'vk-adnetwork' ); ?> (VK AdNetwork)
    </label>
    </div>

  <?php do_action( 'vk-adnetwork-output-metabox-after', $ad ); ?>

</div>
