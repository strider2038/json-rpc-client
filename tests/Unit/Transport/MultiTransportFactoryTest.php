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
use Psr\Log\NullLogger;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Transport\Http\SymfonyTransport;
use Strider2038\JsonRpcClient\Transport\MultiTransportFactory;
use Strider2038\JsonRpcClient\Transport\SocketTransport;
use Strider2038\JsonRpcClient\Transport\TransportLoggingDecorator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MultiTransportFactoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider connectionStringAndExpectedTransportClass
     */
    public function createTransport_givenConnection_highLevelClientWithExpectedTransportCreatedAndReturned(
        string $connection,
        string $transportClass
    ): void {
        $factory = new MultiTransportFactory();

        $transport = $factory->createTransport($connection, new GeneralOptions());

        $this->assertInstanceOf($transportClass, $transport);
    }

    public function connectionStringAndExpectedTransportClass(): \Iterator
    {
        yield ['tcp://localhost:3000', SocketTransport::class];
        yield ['unix:///var/run/jsonrpc.sock', SocketTransport::class];
        yield ['http://localhost:3000', SymfonyTransport::class];
        yield ['https://localhost:3000', SymfonyTransport::class];
    }

    /** @test */
    public function createTransport_notSupportedConnection_exceptionThrown(): void
    {
        $factory = new MultiTransportFactory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Unsupported protocol: "unknown". Supported protocols: "unix", "tcp", "http", "https".');

        $factory->createTransport('unknown://localhost:3000', new GeneralOptions());
    }

    /** @test */
    public function createClient_tcpConnectionAndLoggerIsUsed_transportIsDecoratedWithLogger(): void
    {
        $factory = new MultiTransportFactory(new NullLogger());

        $transport = $factory->createTransport('tcp://localhost:3000', new GeneralOptions());

        $this->assertInstanceOf(TransportLoggingDecorator::class, $transport);
    }
}
