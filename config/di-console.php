<?php

declare(strict_types=1);

use Yii3Swoole\Command\StartCommand;
use Yii3Swoole\Server;

/** @var array $params */

return [
    Server::class => [
        '__construct()' => [
            'rootPath' => $params['yiisoft/yii-runner-http']['rootPath'] ?? dirname(__DIR__),
            'debug' => $params['yiisoft/yii-runner-http']['debug'] ?? false,
            'checkEvents' => $params['yiisoft/yii-runner-http']['checkEvents'] ?? false,
            'environment' => $params['yiisoft/yii-runner-http']['environment'] ?? null,
        ],
    ],
];
