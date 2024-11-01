<?php
defined('ABSPATH') || exit;

/** 
 * VK AdNetwork capabilities
 * 
 * currently only for informational purposes
 */

$vk_adnetwork_capabilities = apply_filters( 'vk-adnetwork-capabilities', [
    'vk_adnetwork_manage_options',      // admins only
    'vk_adnetwork_see_interface',       // admins, maybe editors
    'vk_adnetwork_edit_ads',            // admins, maybe editors
    'vk_adnetwork_manage_placements',   // admins, maybe editors
    'vk_adnetwork_place_ads',           // admins, maybe editors
]);