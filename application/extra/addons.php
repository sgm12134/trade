<?php

return [
    'autoload' => false,
    'hooks' => [
        'run' => [
            'voicenotice',
        ],
        'action_begin' => [
            'voicenotice',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
