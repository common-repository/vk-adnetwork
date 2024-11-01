<?php
defined( 'ABSPATH' ) || exit;

/**
 * VK_Adnetwork_Admin_Settings > settings_init > render_settings_vk_adnetwork_creds
 *
 * @var $client_id
 * @var $client_secret
 * @var $access_token
 * @var $refresh_token
 * @var $tokens_left
 * @var $delete_tokens
 * @var $options
 */
?>
<?php wp_nonce_field( 'vk_adnetwork-options', 'vk_adnetwork_options_nonce'); ?>
<div class="separator"></div>
<table class="credentials-table" border-spacing="0">
    <tr>
        <td>
            <span class="field-label"><?php esc_html_e( 'client_id', 'vk-adnetwork' ); ?></span>
        </td><td>
            <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip">
                    <?php printf(
                            // Эту информацию можно найти в Вашем <a href="%s" target="blank">профиле VK AdNetwork</a>
                            // translators: %s is the address of the user's profile page on the VK advertising network
                            wp_kses(__( 'This information can be found in your <a href="%s" target="blank">VK AdNetwork profile</a>', 'vk-adnetwork' ), ['a' => ['href' => true]]),
                            esc_url(VK_ADNETWORK_URL . 'hq/settings')
                    ) ?>
            </span></span>
        </td><td>
            <label>
                <input
                    id="vk-adnetwork-creds-client_id"
                    type="text"
                    value="<?php echo esc_attr($client_id); ?>"
                    name="<?php echo esc_attr(VK_ADNETWORK_SLUG); ?>[vk-adnetwork-creds][client_id]"
                >
            </label>
        </td><td>
        </td>
    </tr>

    <tr>
        <td>
            <span class="field-label"><?php esc_html_e( 'client_secret', 'vk-adnetwork' ); ?></span>
        </td><td>
            <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip">
                <?php printf(
                        // Эту информацию можно найти в Вашем <a href="%s" target="blank">профиле VK AdNetwork</a>
                        // translators: %s is the address of the user's profile page on the VK advertising network
                        wp_kses(__( 'This information can be found in your <a href="%s" target="blank">VK AdNetwork profile</a>', 'vk-adnetwork' ), ['a' => ['href' => true, 'target' => true]]),
                        esc_url(VK_ADNETWORK_URL . 'hq/settings')
                ) ?>
            </span></span>
        </td><td>
            <label>
                <input
                    id="vk-adnetwork-creds-client_secret"
                    type="text"
                    value="<?php echo esc_attr($client_secret); ?>"
                    name="<?php echo esc_attr(VK_ADNETWORK_SLUG); ?>[vk-adnetwork-creds][client_secret]"
                >
            </label>
        </td><?php /* <td>
            <a id="xmore">больше</a>
            <script>
                document.getElementById('xmore').onclick = function() {
                    mr = document.getElementById('more')
                    mr.style.display = mr.style.display === 'none' ? 'block' : 'none'
                };
            </script>
        </td>
        */ ?>
    </tr>

</table>
<?php /*
 хотя бы в консоли давай оставим возможность сделать )) -- document.getElementById('more').style.display = 'block'
*/ ?>
<table id="more" border="0" class="credentials-table" style="display: none">
    <tr>
        <td>
            <span class="field-label"><?php esc_html_e( 'access_token', 'vk-adnetwork' ); ?></span>
        </td><td>
            <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip">
                <?php
                esc_html_e( 'About access_token ', 'vk-adnetwork' );
                // (это поле заполнять не надо, оно заполнится само из клиент_ида/клиент_секрета)
                esc_html_e( '(this field does not need to be filled in, it will be filled in by itself from the client_id/client_secret)', 'vk-adnetwork' );
                ?>
            </span></span>
        </td><td>
            <label>
                <input
                    id="vk-adnetwork-creds-access_token"
                    type="text"
                    value="<?php echo esc_attr($access_token); ?>"
                    name="<?php echo esc_attr(VK_ADNETWORK_SLUG); ?>[vk-adnetwork-creds][access_token]"
                >
            </label>
        </td><td></td>
    </tr>

    <tr>
        <td>
            <span class="field-label"><?php esc_html_e( 'refresh_token', 'vk-adnetwork' ); ?></span>
        </td><td>
            <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip">
                <?php
                esc_html_e( 'About refresh_token ', 'vk-adnetwork' );
                // (это поле заполнять не надо, оно заполнится само из клиент_ида/клиент_секрета)
                esc_html_e( '(this field does not need to be filled in, it will be filled in by itself from the client_id/client_secret)', 'vk-adnetwork' );
                ?>
            </span></span>
        </td><td>
            <label>
                <input
                    id="vk-adnetwork-creds-refresh_token"
                    type="text"
                    value="<?php echo esc_attr($refresh_token); ?>"
                    name="<?php echo esc_attr(VK_ADNETWORK_SLUG); ?>[vk-adnetwork-creds][refresh_token]"
                >
            </label>
        </td><td>
        </td>
    </tr>

    <tr>
        <td>
            <span class="field-label"><?php esc_html_e( 'Tokens left', 'vk-adnetwork' ); ?></span>
        </td><td>
            <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip">
                <?php
                esc_html_e( 'About tokens_left ', 'vk-adnetwork' );
                // (сколько токенов осталось)
                esc_html_e( '(how many tokens are left)', 'vk-adnetwork' );
                ?>
            </span></span>
        </td><td>
            <label>
                <input disabled="disabled"
                    id="vk-adnetwork-creds-tokens_left"
                    type="text"
                    value="<?php echo absint($tokens_left); ?>"
                    name="<?php echo esc_attr(VK_ADNETWORK_SLUG); ?>[vk-adnetwork-creds][tokens_left]"
                >
            </label>
        </td><td>
            <label>
                <input id="vk-adnetwork-creds-tokens_delete"
                       type="checkbox" value="1"
                       name="<?php echo esc_attr( VK_ADNETWORK_SLUG ); ?>[vk-adnetwork-creds][delete_tokens]"
                    <?php
                    checked( $delete_tokens, 1 );
                    ?>
                > <?php esc_html_e( 'Delete tokens', 'vk-adnetwork' ); ?>
            </label>
            <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip">
                <?php
                esc_html_e( 'About delete_tokens ', 'vk-adnetwork' );
                // (удалить все токены)
                esc_html_e( '(delete all tokens)', 'vk-adnetwork' );
                ?>
            </span></span>
        </td>
    </tr>
    <tr>
        <td>
            <span class="field-label"><?php esc_html_e( 'group_id', 'vk-adnetwork' ); ?></span>
        </td><td>
            <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip">
                <?php
                // Идер площадки
                esc_html_e( 'Ider of the site ', 'vk-adnetwork' );
                // (это поле заполнять не надо, оно заполнится само при создании площадки)
                esc_html_e( '(you do not need to fill in this field, it will be filled in by itself when creating the site) ', 'vk-adnetwork' );
                // (если вставить сюда другой номер площадки - то новые пады будут создаваться в этой площадке)
                esc_html_e( '(if you insert another site number here, then new pads will be created in this site)', 'vk-adnetwork' );
                ?>
            </span></span>
        </td><td>
            <label>
                <input
                        id="vk-adnetwork-group_id"
                        type="text"
                        value="<?php echo esc_attr($options['group_id'] ?? ''); ?>"
                        name="<?php echo esc_attr(VK_ADNETWORK_SLUG); ?>[group_id]"
                >
            </label>
        </td><td>
        </td>
    </tr>

    <tr>
        <td>
            <span class="field-label"><?php esc_html_e( 'pad_id', 'vk-adnetwork' ); ?></span>
        </td><td>
            <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip">
                <?php
                // Идер (последнего созданного) блока
                esc_html_e( 'Id of the (last created) block ', 'vk-adnetwork' );
                // (это поле заполнять не надо, оно заполнится само при создании блока)
                esc_html_e( '(this field does not need to be filled in, it will be filled in by itself when creating the block)', 'vk-adnetwork' );
                ?>
            </span></span>
        </td><td>
            <label>
                <input
                        id="vk-adnetwork-pad_id"
                        type="text"
                        value="<?php echo esc_attr($options['pad_id'] ?? ''); ?>"
                        name="<?php echo esc_attr(VK_ADNETWORK_SLUG); ?>[pad_id]"
                >
            </label>
        </td><td>
        </td>
    </tr>

    <tr>
        <td>
            <span class="field-label"><?php esc_html_e( 'slot_id', 'vk-adnetwork' ); ?></span>
        </td><td>
            <span class="vk_adnetwork-help"><span class="vk_adnetwork-tooltip">
                <?php
                // Идер слота (последнего созданного) блока
                esc_html_e( 'ID of the slot of the (last created) block ', 'vk-adnetwork' );
                // (это поле заполнять не надо, оно заполнится само при создании блока)
                esc_html_e( '(this field does not need to be filled in, it will be filled in by itself when creating the block)', 'vk-adnetwork' );
                ?>
            </span></span>
        </td><td>
            <label>
                <input
                        id="vk-adnetwork-slot_id"
                        type="text"
                        value="<?php echo esc_attr($options['slot_id'] ?? ''); ?>"
                        name="<?php echo esc_attr(VK_ADNETWORK_SLUG); ?>[slot_id]"
                >
            </label>
        </td><td>
        </td>
    </tr>

</table>
<!--*/ ?-->
<div class="separator no-spacing"></div>
