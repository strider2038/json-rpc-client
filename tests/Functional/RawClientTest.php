<?php

namespace Strider2038\JsonRpcClient\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\ClientBuilder;
use Strider2038\JsonRpcClient\ClientInterface;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

class RawClientTest extends TestCase
{
    /** @var TransportInterface */
    private $transport;

    protected function setUp(): void
    {
        $this->transport = \Phake::mock(TransportInterface::class);
    }

    /** @test */
    public function singleRequest_positionalParameters_resultReturned(): void
    {
        $client = $this->createRawClient();
        $this->givenResponseFromServer('{"jsonrpc": "2.0", "result": 19, "id": 1}');

        /** @var ResponseObjectInterface $response */
        $response = $client->call('subtract', [42, 23]);

        $this->assertInstanceOf(ResponseObjectInterface::class, $response);
        $this->assertFalse($response->hasError());
        $this->assertRequestWasSentByTransport();
        $this->assertSame(19, $response->getResult());
    }

    /** @test */
    public function singleRequest_namedParameters_resultReturned(): void
    {
        $client = $this->createRawClient();
        $this->givenResponseFromServer('{"jsonrpc": "2.0", "result": 19, "id": 1}');
        $params = new \stdClass();
        $params->subtrahend = 23;
        $params->minuend = 42;

        /** @var ResponseObjectInterface $response */
        $response = $client->call('subtract', $params);

        $this->assertInstanceOf(ResponseObjectInterface::class, $response);
        $this->assertFalse($response->hasError());
        $this->assertRequestWasSentByTransport();
        $this->assertSame(19, $response->getResult());
    }

    /** @test */
    public function singleNotification_positionalParameters_nullReturned(): void
    {
        $client = $this->createRawClient();
        $this->givenResponseFromServer('');

        $client->notify('notify', [1, 2, 3]);

        $this->assertRequestWasSentByTransport();
    }

    /** @test */
    public function nonExistentMethod_positionalParameters_exceptionThrown(): void
    {
        $client = $this->createRawClient();
        $this->givenResponseFromServer(
            '{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "1"}'
        );

        /** @var ResponseObjectInterface $response */
        $response = $client->call('non-existent-method', [1, 2, 3]);

        $this->assertInstanceOf(ResponseObjectInterface::class, $response);
        $this->assertTrue($response->hasError());
        $this->assertSame(-32601, $response->getError()->getCode());
        $this->assertSame('Method not found', $response->getError()->getMessage());
        $this->assertNull($response->getError()->getData());
    }

    /** @test */
    public function batchRequest_validParameters_orderedResultsReturned(): void
    {
        $client = $this->createRawClient();
        $this->givenResponseFromServer('
        [
            {"jsonrpc": "2.0", "result": 19, "id": 2},
            {"jsonrpc": "2.0", "result": 7, "id": 1},
            {"jsonrpc": "2.0", "result": {"key": "value"}, "id": 3}
        ]
        ');

        /** @var ResponseObjectInterface[] $responses */
        $responses = $client->batch()
            ->call('sum', [1, 2, 4])
            ->notify('notify_hello', [7])
            ->call('subtract', [42, 23])
            ->call('getData')
            ->send();

        $this->assertRequestWasSentByTransport();
        $this->assertIsArray($responses);
        $this->assertCount(3, $responses);
        $this->assertSame(19, $responses[0]->getResult());
        $this->assertSame(7, $responses[1]->getResult());
        $this->assertSame(['key' => 'value'], (array) $responses[2]->getResult());
    }

    /** @test */
    public function batchNotification_validParameters_nullResultsReturned(): void
    {
        $client = $this->createRawClient();
        $this->givenResponseFromServer('');

        $responses = $client->batch()
            ->notify('notify_sum', [1, 2, 4])
            ->notify('notify_hello', [2])
            ->send();

        $this->assertRequestWasSentByTransport();
        $this->assertIsArray($responses);
        $this->assertCount(0, $responses);
    }

    private function createRawClient(): ClientInterface
    {
        $clientBuilder = new ClientBuilder($this->transport);
        $clientBuilder = $clientBuilder->disableResponseProcessing();

        return $clientBuilder->getClient();
    }

    private function assertRequestWasSentByTransport(): void
    {
        \Phake::verify($this->transport)
            ->send(\Phake::anyParameters());
    }

    private function givenResponseFromServer(string $response): void
    {
        \Phake::when($this->transport)
            ->send(\Phake::anyParameters())
            ->thenReturn($response);
    }
}
