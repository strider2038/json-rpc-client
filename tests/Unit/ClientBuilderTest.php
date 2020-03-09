<?php
/*
 * This file is part of json-rpc-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\ClientBuilder;
use Strider2038\JsonRpcClient\Request\IdGeneratorInterface;
use Strider2038\JsonRpcClient\Serialization\MessageSerializerInterface;
use Strider2038\JsonRpcClient\Service\ProcessingClient;
use Strider2038\JsonRpcClient\Service\RawClient;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientBuilderTest extends TestCase
{
    /** @test */
    public function getClient_noAdditionalOptions_highLevelClientReturnedByDefault(): void
    {
        $transport = \Phake::mock(TransportInterface::class);

        $clientBuilder = new ClientBuilder($transport);
        $client = $clientBuilder->getClient();

        $this->assertInstanceOf(ProcessingClient::class, $client);
    }

    /** @test */
    public function getClient_disableResponseProcessing_lowLevelClient(): void
    {
        $transport = \Phake::mock(TransportInterface::class);

        $clientBuilder = new ClientBuilder($transport);
        $clientBuilder->disableResponseProcessing();
        $client = $clientBuilder->getClient();

        $this->assertInstanceOf(RawClient::class, $client);
    }

    /** @test */
    public function getClient_fullCustomizationWithFluentInterface_lowLevelClient(): void
    {
        $transport = \Phake::mock(TransportInterface::class);

        $client = (new ClientBuilder($transport))
            ->setSerializer(\Phake::mock(MessageSerializerInterface::class))
            ->setIdGenerator(\Phake::mock(IdGeneratorInterface::class))
            ->disableResponseProcessing()
            ->getClient();

        $this->assertInstanceOf(RawClient::class, $client);
    }
}
