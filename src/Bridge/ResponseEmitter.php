<?php

declare(strict_types=1);

namespace Yii3Swoole\Bridge;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;

final class ResponseEmitter
{
    public function emit(ResponseInterface $response, SwooleResponse $swooleResponse): void
    {
        // Status Code
        $swooleResponse->status($response->getStatusCode(), $response->getReasonPhrase());

        // Headers
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }

        // Body
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        // Swoole response->write supports chunks, or end() for the whole content.
        // For simplicity, we can just send the whole body if it's small, or chunk it.
        // Let's just use end() with string content for now.
        // If content is large, we should use write() in chunks.

        $swooleResponse->end($body->getContents());
    }
}
