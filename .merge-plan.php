<?php

declare(strict_types=1);

// Do not edit. Content will be replaced.
return [
    '/' => [
        'di-console' => [
            'yiisoft/yii-console' => [
                'config/di-console.php',
            ],
            'yiisoft/yii-event' => [
                'config/di-console.php',
            ],
        ],
        'events-console' => [
            'yiisoft/yii-console' => [
                'config/events-console.php',
            ],
            'yiisoft/log' => [
                'config/events-console.php',
            ],
        ],
        'params-console' => [
            'yiisoft/yii-console' => [
                'config/params-console.php',
            ],
            'yiisoft/yii-event' => [
                'config/params-console.php',
            ],
        ],
        'di-web' => [
            'yiisoft/error-handler' => [
                'config/di-web.php',
            ],
            'yiisoft/yii-event' => [
                'config/di-web.php',
            ],
        ],
        'di' => [
            'yiisoft/log-target-file' => [
                'config/di.php',
            ],
            'yiisoft/yii-event' => [
                'config/di.php',
            ],
        ],
        'params' => [
            'yiisoft/log-target-file' => [
                'config/params.php',
            ],
        ],
        'events-web' => [
            'yiisoft/log' => [
                'config/events-web.php',
            ],
            'yiisoft/middleware-dispatcher' => [
                'config/events-web.php',
            ],
        ],
        'params-web' => [
            'yiisoft/yii-event' => [
                'config/params-web.php',
            ],
        ],
    ],
];
