<?php
defined( 'ABSPATH' ) || exit;

/**
 * Render schedule options in the publish meta box on the ad edit screen
 *
 * VK_Adnetwork_Admin_Ad_Type > add_submit_box_meta
 *
 * @var int $curr_month current month index;
 * @var $wp_locale
 * @var $curr_day
 * @var $curr_year
 * @var $curr_hour
 * @var $curr_minute
 * @var $curr_month
 * @var $enabled
 */
?><div id="vk-adnetwork-expiry-date" class="misc-pub-section curtime misc-pub-curtime">
    <label onclick="vk_adnetwork_toggle_box('#vk-adnetwork-expiry-date-enable', '#vk-adnetwork-expiry-date .inner')">
        <input type="checkbox" id="vk-adnetwork-expiry-date-enable" name="vk_adnetwork[expiry_date][enabled]" value="1" <?php checked( $enabled, 1 ); ?>/><?php esc_html_e( 'End ad serving', 'vk-adnetwork' ); ?>
    </label>
    <br/>
    <div class="inner" style="display:<?php echo esc_attr( $enabled ? 'block' : 'none') ?>;">
        <?php
        $month_field = '<label><span class="screen-reader-text">' . esc_html__( 'Month', 'vk-adnetwork' ) . '</span><select class="vk_adnetwork-mm" name="vk_adnetwork[expiry_date][month]"' . ">\n";
        for ( $i = 1; $i < 13; $i = ++$i ) {
            $month_num    = zeroise( $i, 2 );
            $month_field .= "\t\t\t" . '<option value="' . $month_num . '" ' . selected( $curr_month, $month_num, false ) . '>';
            $month_field .= sprintf(
                // translators: %1$s is the month number, %2$s is the month shortname.
                esc_html_x( '%1$s-%2$s', '1: month number (01, 02, etc.), 2: month abbreviation', 'vk-adnetwork' ),
                $month_num,
                $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) )
            ) . "</option>\n";
        }
            $month_field .= '</select></label>';

            $day_field    = '<label><span class="screen-reader-text">' . esc_html__( 'Day', 'vk-adnetwork' ) . '</span><input type="text" class="vk_adnetwork-jj" name="vk_adnetwork[expiry_date][day]" value="' . $curr_day . '" size="2" maxlength="2" autocomplete="off" /></label>';
            $year_field   = '<label><span class="screen-reader-text">' . esc_html__( 'Year', 'vk-adnetwork' ) . '</span><input type="text" class="vk_adnetwork-aa" name="vk_adnetwork[expiry_date][year]" value="' . $curr_year . '" size="4" maxlength="4" autocomplete="off" /></label>';
            $hour_field   = '<label><span class="screen-reader-text">' . esc_html__( 'Hour', 'vk-adnetwork' ) . '</span><input type="text" class="vk_adnetwork-hh" name="vk_adnetwork[expiry_date][hour]" value="' . $curr_hour . '" size="2" maxlength="2" autocomplete="off" /></label>';
            $minute_field = '<label><span class="screen-reader-text">' . esc_html__( 'Minute', 'vk-adnetwork' ) . '</span><input type="text" class="vk_adnetwork-mn" name="vk_adnetwork[expiry_date][minute]" value="' . $curr_minute . '" size="2" maxlength="2" autocomplete="off" /></label>';

        ?>
        <fieldset class="vk_adnetwork-timestamp">
                <?php
                echo wp_kses(
                    sprintf(
                        // translators: %1$s month, %2$s day, %3$s year, %4$s hour, %5$s minute.
                        esc_html_x( '%1$s %2$s, %3$s at %4$s %5$s', 'order of expiry date fields 1: month, 2: day, 3: year, 4: hour, 5: minute', 'vk-adnetwork' ),
                        $month_field,
                        $day_field,
                        $year_field,
                        $hour_field,
                        $minute_field
                    ),
                    [
                        'label'  => true,
                        'span'   => ['class' => true],
                        'select' => ['class' => true, 'name' => true],
                        'option' => ['value' => true],
                        'input'  => ['class' => true, 'name' => true, 'type' => true, 'value' => true, 'size' => true, 'maxlength' => true, 'autocomplete' => true]
                    ]
                );
                ?>
        </fieldset>
        (<?php echo esc_html( VK_Adnetwork_Utils::vk_adnetwork_get_timezone_name() ); ?>)
    </div>
</div>
