<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK_Adnetwork_Admin > wp_plugins_loaded > add_deactivation_logic
 *
 * @var $email
 * @var $from
 */
?>
<div id="vk-adnetwork-feedback-overlay" style="display: none;">
    <div id="vk-adnetwork-feedback-content">
    <span id="vk-adnetwork-feedback-overlay-close-button">&#x2715;</span>
        <form action="" method="post">
            <p><strong><?php esc_attr_e( 'Why did you decide to disable VK AdNetwork?', 'vk-adnetwork' ); ?></strong></p>
            <ul>
            <li class="vk_adnetwork_disable_help"><label><input type="radio" name="vk_adnetwork_disable_reason" value="get help" checked="checked"/><?php esc_attr_e( 'I have a problem, a question or need help.', 'vk-adnetwork' ); ?></label></li>
            <li><textarea class="vk_adnetwork_disable_help_text" id="vk_adnetwork_disable_textarea" name="vk_adnetwork_disable_text[]" placeholder="<?php esc_attr_e( 'Please let us know how we can help', 'vk-adnetwork' ); ?>"></textarea></li>
            <?php if ( $email ) : ?>
                <?php $mailinput = '<input type="email" id="vk_adnetwork_disable_reply_email" name="vk_adnetwork_disable_reply_email" value="' . esc_attr( $email ) . '"/>'; ?>
                <li class="vk_adnetwork_disable_reply"><label>
                <?php
                printf(
                    /* translators: %s is the email address of the current user */
                    esc_html__( 'Send me free help to %s', 'vk-adnetwork' ),
                    // see content, HTML and escaping of $mailinput above.
                    // phpcs:ignore
                    wp_kses($mailinput, ['input' => ['type' => true, 'name' => true, 'value' => true]])
                );
                ?>
                    </label></li>
            <?php endif; ?>
            <li><label><input type="radio" name="vk_adnetwork_disable_reason" value="ads not showing up"/><?php esc_attr_e( 'Ads are not showing up', 'vk-adnetwork' ); ?></label></li>
            <li><label><input type="radio" name="vk_adnetwork_disable_reason" value="temporary"/><?php esc_attr_e( 'It is only temporary', 'vk-adnetwork' ); ?></label></li>
            <li><label><input type="radio" name="vk_adnetwork_disable_reason" value="missing feature"/><?php esc_attr_e( 'I miss a feature', 'vk-adnetwork' ); ?></label></li>
            <li><input type="text" id="vk_adnetwork_disable_text" name="vk_adnetwork_disable_text[]" value="" placeholder="<?php esc_attr_e( 'Which one?', 'vk-adnetwork' ); ?>"/></li>
            <li><label><input type="radio" name="vk_adnetwork_disable_reason" value="stopped showing ads"/><?php esc_attr_e( 'I stopped using ads on my site.', 'vk-adnetwork' ); ?></label></li>
            <li><label><input type="radio" name="vk_adnetwork_disable_reason" value="other plugin"/><?php esc_attr_e( 'I switched to another plugin', 'vk-adnetwork' ); ?></label></li>
            </ul>
            <?php if ( $from ) : ?>
                <input type="hidden" id="vk_adnetwork_disable_from" name="vk_adnetwork_disable_from" value="<?php echo esc_attr( $from ); ?>"/>
            <?php endif; ?>
            <input class="vk-adnetwork-feedback-submit button button-primary" type="submit" name="vk_adnetwork_disable_submit" value="<?php esc_attr_e( 'Send feedback & deactivate', 'vk-adnetwork' ); ?>"/>
            <input class="vk-adnetwork-feedback-not-deactivate vk-adnetwork-feedback-submit button" type="submit" name="vk_adnetwork_keep_submit" value="<?php esc_attr_e( 'Send feedback', 'vk-adnetwork' ); ?>">
            <?php wp_nonce_field( 'vk_adnetwork_disable_form', 'vk_adnetwork_disable_form_nonce' ); ?>
            <a class="vk-adnetwork-feedback-only-deactivate" href="#"><?php esc_attr_e( 'Only Deactivate', 'vk-adnetwork' ); ?></a>
        </form>
        <div id="vk-adnetwork-feedback-after-submit">
            <h2 id="vk-adnetwork-feedback-after-submit-waiting" style="display: none"><?php esc_attr_e( 'Thanks for submitting your feedback. I will reply within 24 hours on working days.', 'vk-adnetwork' ); ?></h2>
            <h2 id="vk-adnetwork-feedback-after-submit-goodbye" style="display: none">
            <?php
                // translators: %s is the title of the website.
                printf( esc_attr__( 'All the best to you and %s.', 'vk-adnetwork' ), '<em>' . esc_html( get_bloginfo( 'name' ) ) . '</em>' );
            ?>
                </h2>
            <p id="vk-adnetwork-feedback-after-submit-disabling-plugin" style="display: none"><?php esc_attr_e( 'Disabling the plugin now…', 'vk-adnetwork' ); ?></p>
        </div>
    </div>
</div>
