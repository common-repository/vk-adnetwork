<?php
defined('ABSPATH') || exit;

/**
 * options\moderation\issues > [] and != GROUP_PAD_ON_MODERATION
 *
 * @var array $issues - issues from VK AdNetwork
 */
?>
<?php if ($issues) { ?>
  <h2><?php // Модерация не пройдена
    esc_html_e( 'Moderation not passed', 'vk-adnetwork' );
  ?></h2>
  <ol>
    <?php

    $issues['GROUP_PAD_ON_MODERATION'] = __('GROUP_PAD_ON_MODERATION', 'vk-adnetwork');  // The GroupPad is on moderation.
    $issues['NO_ACTIVE_PADS']          = __('NO_ACTIVE_PADS',          'vk-adnetwork');  // The GroupPad has no active pads.
    foreach ($issues as $issue) {
      echo wp_kses("<li>$issues[$issue]</li>", ['li' => true]);
    }
    ?>
  </ol>
<?php } ?>
