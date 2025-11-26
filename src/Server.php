<?php

declare(strict_types=1);

namespace Yii3Swoole;

use Psr\Container\ContainerInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Server as SwooleServer;
use Throwable;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Yii\Http\Application;
use Yiisoft\Yii\Http\Handler\ThrowableHandler;
use Yiisoft\Yii\Runner\ApplicationRunner;
use Yii3Swoole\Bridge\RequestConverter;
use Yii3Swoole\Bridge\ResponseEmitter;

final class Server extends ApplicationRunner
{
    private string $host = '127.0.0.1';
    private int $port = 9501;
    private array $options = [];
    private ?ErrorHandler $temporaryErrorHandler = null;

    public function __construct(
        string $rootPath,
        bool $debug = false,
        bool $checkEvents = false,
        ?string $environment = null,
        string $bootstrapGroup = 'bootstrap-web',
        string $eventsGroup = 'events-web',
        string $diGroup = 'di-web',
        string $diProvidersGroup = 'di-providers-web',
        string $diDelegatesGroup = 'di-delegates-web',
        string $diTagsGroup = 'di-tags-web',
        string $paramsGroup = 'params-web',
        array $nestedParamsGroups = ['params'],
        array $nestedEventsGroups = ['events'],
        array $configModifiers = [],
        string $configDirectory = 'config',
        string $vendorDirectory = 'vendor',
        string $configMergePlanFile = '.merge-plan.php',
    ) {
        parent::__construct(
            $rootPath,
            $debug,
            $checkEvents,
            $environment,
            $bootstrapGroup,
            $eventsGroup,
            $diGroup,
            $diProvidersGroup,
            $diDelegatesGroup,
            $diTagsGroup,
            $paramsGroup,
            $nestedParamsGroups,
            $nestedEventsGroups,
            $configModifiers,
            $configDirectory,
            $vendorDirectory,
            $configMergePlanFile,
        );
    }

    public function withHost(string $host): self
    {
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    public function withPort(int $port): self
    {
        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    public function withOptions(array $options): self
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

    public function run(): void
    {
        $server = new SwooleServer($this->host, $this->port);
        if (!empty($this->options)) {
            $server->set($this->options);
        }

        $server->on('WorkerStart', function (SwooleServer $server, int $workerId) {
            // Bootstrap Yii3 in each worker
            $this->bootstrap();
        });

        $server->on('request', function (SwooleRequest $request, SwooleResponse $response) {
            $this->handleRequest($request, $response);
        });

        $server->start();
    }

    private function bootstrap(): void
    {
        // Register temporary error handler
        $temporaryErrorHandler = $this->createTemporaryErrorHandler();
        $this->registerErrorHandler($temporaryErrorHandler);

        $container = $this->getContainer();

        // Register actual error handler
        /** @var ErrorHandler $actualErrorHandler */
        $actualErrorHandler = $container->get(ErrorHandler::class);
        $this->registerErrorHandler($actualErrorHandler, $temporaryErrorHandler);

        $this->runBootstrap();
        $this->checkEvents();
    }

    private function handleRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        $container = $this->getContainer();
        /** @var Application $application */
        $application = $container->get(Application::class);

        $converter = new RequestConverter();
        $emitter = new ResponseEmitter();

        $psrRequest = $converter->convert($swooleRequest);

        try {
            $application->start();
            $psrResponse = $application->handle($psrRequest);
            $emitter->emit($psrResponse, $swooleResponse);
        } catch (Throwable $throwable) {
            $handler = new ThrowableHandler($throwable);
            /** @var ErrorCatcher $errorCatcher */
            $errorCatcher = $container->get(ErrorCatcher::class);
            $psrResponse = $errorCatcher->process($psrRequest, $handler);
            $emitter->emit($psrResponse, $swooleResponse);
        } finally {
            $application->afterEmit($psrResponse ?? null);
            $application->shutdown();
        }
    }

    private function createTemporaryErrorHandler(): ErrorHandler
    {
        if ($this->temporaryErrorHandler !== null) {
            return $this->temporaryErrorHandler;
        }

        $logger = new Logger([new FileTarget("$this->rootPath/runtime/logs/app.log")]);
        return new ErrorHandler($logger, new HtmlRenderer());
    }

    private function registerErrorHandler(ErrorHandler $registered, ErrorHandler $unregistered = null): void
    {
        $unregistered?->unregister();

        if ($this->debug) {
            $registered->debug();
        }

        $registered->register();
    }
}
