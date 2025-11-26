<?php

declare(strict_types=1);

namespace Yii3Swoole\Bridge;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;

final class RequestConverter
{
    private Psr17Factory $factory;

    public function __construct()
    {
        $this->factory = new Psr17Factory();
    }

    public function convert(SwooleRequest $swooleRequest): ServerRequestInterface
    {
        $server = $swooleRequest->server;
        $header = $swooleRequest->header ?? [];
        $cookie = $swooleRequest->cookie ?? [];
        $get = $swooleRequest->get ?? [];
        $post = $swooleRequest->post ?? [];
        $files = $swooleRequest->files ?? [];

        // Method
        $method = $server['request_method'] ?? 'GET';

        // URI
        $uri = $this->factory->createUri(
            ($server['request_uri'] ?? '/') .
            (isset($server['query_string']) ? '?' . $server['query_string'] : '')
        );

        if (isset($header['host'])) {
            $uri = $uri->withHost($header['host']);
        }
        
        // Scheme (Swoole doesn't always provide this reliably in server params, usually http or https)
        // We can check if 'https' is 'on' in server params or check port
        $scheme = 'http';
        if (isset($server['https']) && $server['https'] === 'on') {
            $scheme = 'https';
        }
        $uri = $uri->withScheme($scheme);


        // Create Request
        $request = $this->factory->createServerRequest($method, $uri, $server);

        // Headers
        foreach ($header as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // Cookies
        $request = $request->withCookieParams($cookie);

        // Query Params
        $request = $request->withQueryParams($get);

        // Parsed Body
        $request = $request->withParsedBody($post);

        // Uploaded Files
        // TODO: Implement file conversion properly. For now, we skip or do basic mapping.
        // PSR-7 expects UploadedFileInterface.
        // $request = $request->withUploadedFiles($this->convertFiles($files));

        // Body
        $content = $swooleRequest->rawContent();
        if ($content !== false && $content !== '') {
            $stream = $this->factory->createStream($content);
            $request = $request->withBody($stream);
        }

        return $request;
    }
}
