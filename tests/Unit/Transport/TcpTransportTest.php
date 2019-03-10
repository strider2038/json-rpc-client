<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Transport;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Transport\TcpTransport;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class TcpTransportTest extends TestCase
{
    /** @test */
    public function construct_emptyUrlConnection_exceptionThrown(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Valid URL is expected for TCP/IP transport.');

        new TcpTransport('');
    }

    /** @test */
    public function construct_invalidUrlConnection_exceptionThrown(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Valid URL is expected for TCP/IP transport.');

        new TcpTransport('invalid');
    }

    /** @test */
    public function construct_nonTcpUrlConnection_exceptionThrown(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('URL for TCP/IP transport must start with "tcp://" scheme.');

        new TcpTransport('http://example.com');
    }
}
