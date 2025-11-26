<?php

declare(strict_types=1);

namespace Yii3Swoole\Tests\Bridge;

use PHPUnit\Framework\TestCase;
use Swoole\Http\Request as SwooleRequest;
use Yii3Swoole\Bridge\RequestConverter;

final class RequestConverterTest extends TestCase
{
    public function testConvert(): void
    {
        if (!class_exists(SwooleRequest::class)) {
            $this->markTestSkipped('Swoole extension is not installed.');
        }

        $swooleRequest = $this->createMock(SwooleRequest::class);
        $swooleRequest->server = [
            'request_method' => 'GET',
            'request_uri' => '/test',
            'query_string' => 'param=value',
            'protocol_version' => 'HTTP/1.1',
        ];
        $swooleRequest->header = [
            'host' => 'example.com',
            'content-type' => 'application/json',
        ];
        $swooleRequest->get = ['param' => 'value'];
        $swooleRequest->post = [];
        $swooleRequest->cookie = [];
        $swooleRequest->files = [];

        $swooleRequest->method('rawContent')->willReturn('{"foo":"bar"}');

        $converter = new RequestConverter();
        $psrRequest = $converter->convert($swooleRequest);

        $this->assertSame('GET', $psrRequest->getMethod());
        $this->assertSame('http', $psrRequest->getUri()->getScheme());
        $this->assertSame('example.com', $psrRequest->getUri()->getHost());
        $this->assertSame('/test', $psrRequest->getUri()->getPath());
        $this->assertSame('param=value', $psrRequest->getUri()->getQuery());
        $this->assertSame(['param' => 'value'], $psrRequest->getQueryParams());
        $this->assertSame('{"foo":"bar"}', (string) $psrRequest->getBody());
        $this->assertSame(['application/json'], $psrRequest->getHeader('content-type'));
    }
}
