<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Strider2038\JsonRpcClient\ClientFactory;
use Strider2038\JsonRpcClient\ClientInterface;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Service\Caller;
use Strider2038\JsonRpcClient\Service\HighLevelClient;
use Strider2038\JsonRpcClient\Transport\GuzzleHttpTransport;
use Strider2038\JsonRpcClient\Transport\SocketTransport;
use Strider2038\JsonRpcClient\Transport\TransportLoggingDecorator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientFactoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider connectionStringAndExpectedTransportClass
     */
    public function createClient_givenConnection_highLevelClientWithExpectedTransportCreatedAndReturned(
        string $connection,
        string $transportClass
    ): void {
        $factory = new ClientFactory();

        $client = $factory->createClient($connection);

        $this->assertInstanceOf(HighLevelClient::class, $client);
        $this->assertClientHasTransportOfExpectedClass($client, $transportClass);
    }

    public function connectionStringAndExpectedTransportClass(): \Iterator
    {
        yield ['tcp://localhost:3000', SocketTransport::class];
        yield ['http://localhost:3000', GuzzleHttpTransport::class];
        yield ['https://localhost:3000', GuzzleHttpTransport::class];
    }

    /** @test */
    public function createClient_notSupportedConnection_exceptionThrown(): void
    {
        $factory = new ClientFactory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Unsupported protocol: "unknown". Supported protocols: "tcp", "http", "https".');

        $factory->createClient('unknown://localhost:3000');
    }

    /** @test */
    public function createClient_tcpConnectionAndLoggerIsUsed_transportIsDecoratedWithLogger(): void
    {
        $factory = new ClientFactory(new NullLogger());

        $client = $factory->createClient('tcp://localhost:3000');

        $this->assertClientHasTransportOfExpectedClass($client, TransportLoggingDecorator::class);
    }

    private function assertClientHasTransportOfExpectedClass(ClientInterface $client, string $transportClass): void
    {
        $clientReflectionClass = new \ReflectionClass(HighLevelClient::class);
        $callerProperty = $clientReflectionClass->getProperty('caller');
        $callerProperty->setAccessible(true);
        $caller = $callerProperty->getValue($client);

        $callerReflectionClass = new \ReflectionClass(Caller::class);
        $transportProperty = $callerReflectionClass->getProperty('transport');
        $transportProperty->setAccessible(true);
        $transport = $transportProperty->getValue($caller);

        $this->assertInstanceOf($transportClass, $transport);
    }
}
