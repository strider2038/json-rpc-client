<?php
/*
 * This file is part of json-rpc-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\ClientFactory;
use Strider2038\JsonRpcClient\Exception\ErrorResponseException;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;
use Strider2038\JsonRpcClient\Service\HighLevelClient;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class HighLevelHttpClientTest extends TestCase
{
    /** @test */
    public function singleRequest_positionalParameters_resultReturned(): void
    {
        $client = $this->createHighLevelClient();

        $result = $client->call('sum', [1, 2, 4]);

        $this->assertSame(7, $result);
    }

    /** @test */
    public function singleRequest_namedParameters_resultReturned(): void
    {
        $client = $this->createHighLevelClient();
        $params = new \stdClass();
        $params->subtrahend = 23;
        $params->minuend = 42;

        $result = $client->call('subtract', $params);

        $this->assertSame(19, $result);
    }

    /** @test */
    public function nonExistentMethod_positionalParameters_exceptionThrown(): void
    {
        $client = $this->createHighLevelClient();

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage('Server response has error: code -32601');

        $client->call('non-existent-method', [1, 2, 3]);
    }

    /** @test */
    public function batchRequest_validParameters_orderedResultsReturned(): void
    {
        $client = $this->createHighLevelClient();
        $subtractionParams = new \stdClass();
        $subtractionParams->subtrahend = 23;
        $subtractionParams->minuend = 42;
        $reflectParams = new \stdClass();
        $reflectParams->key = 'value';

        $results = $client->batch()
            ->call('sum', [1, 2, 4])
            ->notify('notify', $subtractionParams)
            ->call('subtract', $subtractionParams)
            ->call('reflect', $reflectParams)
            ->send();

        $this->assertIsArray($results);
        $this->assertCount(4, $results);
        $this->assertSame(7, $results[0]);
        $this->assertNull($results[1]);
        $this->assertSame(19, $results[2]);
        $this->assertSame(['key' => 'value'], (array) $results[3]);
    }

    /** @test */
    public function singleRequest_timeout_exceptionThrown(): void
    {
        $client = $this->createHighLevelClient();

        $this->expectException(RemoteProcedureCallFailedException::class);

        $client->call('sleep', [1500]);
    }

    private function createHighLevelClient(): HighLevelClient
    {
        $transportUrl = getenv('TEST_HTTP_TRANSPORT_URL');
        $bearerToken = getenv('TEST_HTTP_BEARER_TOKEN');

        $clientFactory = new ClientFactory();
        $client = $clientFactory->createClient($transportUrl, [
            'transport_configuration' => [
                'headers' => [
                    'Authorization' => 'Bearer '.$bearerToken,
                ],
            ],
        ]);
        assert($client instanceof HighLevelClient);

        return $client;
    }
}
