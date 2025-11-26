# Yii3 Swoole Extension

This extension provides [Swoole](https://www.swoole.com/) support for [Yii3](https://github.com/yiisoft). It allows you to run your Yii3 application using Swoole's high-performance HTTP server.

## Requirements

- PHP 8.4 or higher
- [Swoole PHP Extension](https://www.swoole.com)

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```bash
composer require lantongxue/yii3-swoole
```

## Usage

### 1. Configure the Console Command

Add the `StartCommand` to your console application configuration (e.g., `config/console/di.php` or `di-console.php`):

```php
use Yii3Swoole\Command\StartCommand;
use Yii3Swoole\Server;

return [
    StartCommand::class => [
        '__construct()' => [
            'server' => Server::class,
        ],
    ],
    Server::class => [
        '__construct()' => [
            // Pass necessary ApplicationRunner arguments here
            'rootPath' => $params['rootPath'],
            'debug' => $params['debug'],
            'checkEvents' => $params['checkEvents'],
            'environment' => $params['environment'],
            // ... other runner options
        ],
    ],
];
```

### 2. Start the Server

Run the console command to start the Swoole server:

```bash
./yii swoole/start
```

You can specify the host and port:

```bash
./yii swoole/start --host=0.0.0.0 --port=9501
```

## Features

- **Swoole Server Integration**: Runs Yii3 application within a Swoole HTTP server.
- **PSR-7 Bridge**: Transparently converts Swoole requests to PSR-7 requests and emits PSR-7 responses to Swoole.
- **High Performance**: Leverages Swoole's async capabilities (note: application logic must be async-safe).

## License

The BSD 3-Clause License. Please see [License File](LICENSE.md) for more information.
