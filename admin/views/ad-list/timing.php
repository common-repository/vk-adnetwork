<?php
defined('ABSPATH') || exit;

/**
 * Render content of the Ad Schedule column in the ad overview list
 *
 * Планировщик объявлений: заканчивается 18.05.2023, 00:00
 * VK_Adnetwork_Admin_Ad_Type > ad_list_columns > ad_list_columns_timing
 *
 * @var string $html_classes additonal values for class attribute.
 * @var string $post_future timestamp of the schedule date.
 * @var string $expiry_date_format date format.
 * @var string $expiry expiry date.
 * @var string $content_after HTML to load after the schedule content.
 */
?>
<fieldset class="inline-edit-col-left">
    <div class="inline-edit-col <?php echo esc_attr( $html_classes ); ?>">
        <?php
        if ( $post_future ) :
            ?>
            <div>
            <?php
            printf(
                // translators: %s is a date.
                esc_html__( 'starts %s', 'vk-adnetwork' ),
                esc_html( wp_date( esc_html( $expiry_date_format ), $post_future ) ),
            );
            ?>
                </div>
        <?php endif; ?>
        <?php if ( $expiry ) : ?>
            <?php
            $tz_option   = get_option( 'timezone_string' );
            $expiry_date = date_create( '@' . $expiry, new DateTimeZone( 'UTC' ) );

            if ( $tz_option ) {

                $expiry_date->setTimezone( VK_Adnetwork_Utils::vk_adnetwork_get_wp_timezone() );
                $expiry_date_string = $expiry_date->format( $expiry_date_format );

            } else {

                $tz_name            = VK_Adnetwork_Utils::vk_adnetwork_get_timezone_name();
                $tz_offset          = substr( $tz_name, 3 );
                $off_time           = date_create( $expiry_date->format( 'Y-m-d\TH:i:s' ) . $tz_offset );
                $offset_in_sec      = date_offset_get( $off_time );
                $expiry_date        = date_create( '@' . ( $expiry + $offset_in_sec ) );
                $expiry_date_string = date_i18n( $expiry_date_format, absint( $expiry_date->format( 'U' ) ) );

            }
            ?>
            <?php if ( $expiry > time() ) : ?>
                <div>
                <?php
                printf(
                    // translators: %s is a time and date string.
                    esc_html__( 'expires %s', 'vk-adnetwork' ),
                    esc_html( $expiry_date_string )
                );
                ?>
                    </div>
            <?php else : ?>
                <div>
                <?php
                printf(
                // translators: %s is a time and date string.
                    wp_kses(__( '<strong>expired</strong> %s', 'vk-adnetwork' ), ['strong' => true,]),
                    esc_html( $expiry_date_string )
                );
                ?>
                    </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php
        echo wp_kses_post($content_after);
        ?>
    </div>
</fieldset>
