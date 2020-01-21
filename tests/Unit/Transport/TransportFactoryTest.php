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
use Strider2038\JsonRpcClient\Transport\SocketTransport;
use Strider2038\JsonRpcClient\Transport\TransportFactory;
use Strider2038\JsonRpcClient\Transport\TransportLoggingDecorator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class TransportFactoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider connectionStringAndExpectedTransportClass
     */
    public function createTransport_givenConnection_highLevelClientWithExpectedTransportCreatedAndReturned(
        string $connection,
        string $transportClass
    ): void {
        $factory = new TransportFactory();

        $transport = $factory->createTransport($connection, new GeneralOptions());

        $this->assertInstanceOf($transportClass, $transport);
    }

    public function connectionStringAndExpectedTransportClass(): \Iterator
    {
        yield ['tcp://localhost:3000', SocketTransport::class];
        yield ['http://localhost:3000', \Strider2038\JsonRpcClient\Transport\Http\GuzzleTransport::class];
        yield ['https://localhost:3000', \Strider2038\JsonRpcClient\Transport\Http\GuzzleTransport::class];
    }

    /** @test */
    public function createTransport_notSupportedConnection_exceptionThrown(): void
    {
        $factory = new TransportFactory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Unsupported protocol: "unknown". Supported protocols: "tcp", "http", "https".');

        $factory->createTransport('unknown://localhost:3000', new GeneralOptions());
    }

    /** @test */
    public function createClient_tcpConnectionAndLoggerIsUsed_transportIsDecoratedWithLogger(): void
    {
        $factory = new TransportFactory(new NullLogger());

        $transport = $factory->createTransport('tcp://localhost:3000', new GeneralOptions());

        $this->assertInstanceOf(TransportLoggingDecorator::class, $transport);
    }
}
