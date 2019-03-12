<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\JsonRpcClient;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\ClientFactory;
use Strider2038\JsonRpcClient\ClientInterface;
use Strider2038\JsonRpcClient\Service\Caller;
use Strider2038\JsonRpcClient\Service\HighLevelClient;
use Strider2038\JsonRpcClient\Transport\GuzzleHttpTransport;
use Strider2038\JsonRpcClient\Transport\TcpTransport;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientFactoryTest extends TestCase
{
    /** @test */
    public function createClient_tcpConnection_highLevelClientCreatedAndReturned(): void
    {
        $factory = new ClientFactory();

        $client = $factory->createClient('tcp://localhost:3000');

        $this->assertInstanceOf(HighLevelClient::class, $client);
        $this->assertClientHasTransportOfExpectedClass($client, TcpTransport::class);
    }

    /** @test */
    public function createClient_httpConnection_highLevelClientCreatedAndReturned(): void
    {
        $factory = new ClientFactory();

        $client = $factory->createClient('http://localhost:3000');

        $this->assertInstanceOf(HighLevelClient::class, $client);
        $this->assertClientHasTransportOfExpectedClass($client, GuzzleHttpTransport::class);
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
