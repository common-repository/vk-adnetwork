<?php
defined('ABSPATH') || exit;

/**
 * Array with admin notices
 */
$vk_adnetwork_admin_notices = apply_filters(
    'vk-adnetwork-notices',
    [
        // welcome.
        'nl_intro'        => [
            'type'   => 'info',
            'text'   => VK_Adnetwork_Admin_Notices::get_instance()->get_welcome_panel(),
            'global' => true,
        ],
        // moderation. issues GROUP_PAD_ON_MODERATION
        'nl_moderation'   => [
            'type'   => 'info',
            'text'   => VK_Adnetwork_Admin_Notices::get_instance()->get_moderation_panel(),
            'global' => true,
        ],
        // moderation done. issues === []
        'nl_moderation_done'   => [
            'type'   => 'info',
            'text'   => VK_Adnetwork_Admin_Notices::get_instance()->get_moderation_done_panel(),
            'global' => true,
        ],
        // moderation done. issues === []
        'nl_other_issues'   => [
            'type'   => 'info',
            'text'   => VK_Adnetwork_Admin_Notices::get_instance()->get_other_issues_panel(),
            'global' => true,
        ],
    ]
);

