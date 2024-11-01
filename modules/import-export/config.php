<?php
defined('ABSPATH') || exit;

// module configuration

$path = dirname( __FILE__ );

return [
    'classmap' => [
        'VK_Adnetwork_XmlEncoder' => $path . '/classes/XmlEncoder.php',
        'VK_Adnetwork_Export' => $path . '/classes/export.php',
        'VK_Adnetwork_Import' => $path . '/classes/import.php',
    ],
    'textdomain' => null,
];