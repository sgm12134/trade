<?php

return [
    'autoload' => false,
    'hooks' => [
        'app_init' => [
            'notice',
        ],
        'config_init' => [
            'notice',
            'ueditor',
        ],
        'user_sidenav_after' => [
            'notice',
        ],
        'send_notice' => [
            'notice',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
