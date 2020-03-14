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

use Strider2038\JsonRpcClient\ClientBuilder;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Service\ProcessingClient;
use Strider2038\JsonRpcClient\Tests\TestCase\ClientIntegrationTestCase;
use Strider2038\JsonRpcClient\Transport\Http\Psr18Transport;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Psr18HttpClientTest extends ClientIntegrationTestCase
{
    protected function createClient(): ProcessingClient
    {
        $transportUrl = getenv('TEST_HTTP_TRANSPORT_URL');
        $bearerToken = getenv('TEST_HTTP_BEARER_TOKEN');

        $options = new GeneralOptions();
        $headers = ['Authorization' => 'Bearer '.$bearerToken];
        $timeout = (float) $options->getRequestTimeoutUs() / 1000000;
        $httpClient = HttpClient::create(['timeout' => $timeout]);
        $psr18Client = new Psr18Client($httpClient);
        $psr18Transport = new Psr18Transport($psr18Client, $transportUrl, $headers);

        $clientBuilder = new ClientBuilder($psr18Transport);
        $client = $clientBuilder->getClient();
        assert($client instanceof ProcessingClient);

        return $client;
    }
}
