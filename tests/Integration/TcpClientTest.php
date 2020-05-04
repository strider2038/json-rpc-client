<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\ClientFactory;
use Strider2038\JsonRpcClient\ClientInterface;
use Strider2038\JsonRpcClient\Service\ProcessingClient;
use Strider2038\JsonRpcClient\Tests\TestCase\ClientIntegrationTestCaseTrait;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class TcpClientTest extends TestCase
{
    use ClientIntegrationTestCaseTrait;

    protected function createClient(): ClientInterface
    {
        $transportUrl = getenv('TEST_TCP_TRANSPORT_URL');
        $clientFactory = new ClientFactory();
        $client = $clientFactory->createClient($transportUrl);
        assert($client instanceof ProcessingClient);

        return $client;
    }
}
