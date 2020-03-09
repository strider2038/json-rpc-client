<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Exception\ErrorResponseException;
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;
use Strider2038\JsonRpcClient\Request\SequentialIntegerIdGenerator;
use Strider2038\JsonRpcClient\Response\ExceptionalResponseValidator;
use Strider2038\JsonRpcClient\Serialization\ContextGenerator;
use Strider2038\JsonRpcClient\Serialization\JsonObjectSerializer;
use Strider2038\JsonRpcClient\Service\Caller;
use Strider2038\JsonRpcClient\Service\ProcessingClient;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ProcessingClientTest extends TestCase
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
        $client = $this->createProcessingClient();
        $this->givenResponseFromServer('{"jsonrpc": "2.0", "result": 19, "id": 1}');

        $result = $client->call('subtract', [42, 23]);

        $this->assertRequestWasSentByTransport();
        $this->assertSame(19, $result);
    }

    /** @test */
    public function singleRequest_namedParameters_resultReturned(): void
    {
        $client = $this->createProcessingClient();
        $this->givenResponseFromServer('{"jsonrpc": "2.0", "result": 19, "id": 1}');
        $params = new \stdClass();
        $params->subtrahend = 23;
        $params->minuend = 42;

        $result = $client->call('subtract', $params);

        $this->assertRequestWasSentByTransport();
        $this->assertSame(19, $result);
    }

    /** @test */
    public function singleNotification_positionalParameters_nullReturned(): void
    {
        $client = $this->createProcessingClient();
        $this->givenResponseFromServer('');

        $client->notify('notify', [1, 2, 3]);

        $this->assertRequestWasSentByTransport();
    }

    /** @test */
    public function nonExistentMethod_positionalParameters_exceptionThrown(): void
    {
        $client = $this->createProcessingClient();
        $this->givenResponseFromServer(
            '{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "1"}'
        );

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage('Server response has error: code -32601, message "Method not found", data null.');

        $client->call('non-existent-method', [1, 2, 3]);
    }

    /** @test */
    public function batchRequest_validParameters_orderedResultsReturned(): void
    {
        $client = $this->createProcessingClient();
        $this->givenResponseFromServer('
        [
            {"jsonrpc": "2.0", "result": 19, "id": 2},
            {"jsonrpc": "2.0", "result": 7, "id": 1},
            {"jsonrpc": "2.0", "result": {"key": "value"}, "id": 3}
        ]
        ');

        $results = $client->batch()
            ->call('sum', [1, 2, 4])
            ->notify('notify_hello', [7])
            ->call('subtract', [42, 23])
            ->call('getData')
            ->send();

        $this->assertRequestWasSentByTransport();
        $this->assertIsArray($results);
        $this->assertCount(4, $results);
        $this->assertSame(7, $results[0]);
        $this->assertNull($results[1]);
        $this->assertSame(19, $results[2]);
        $this->assertSame(['key' => 'value'], (array) $results[3]);
    }

    /** @test */
    public function batchNotification_validParameters_nullResultsReturned(): void
    {
        $client = $this->createProcessingClient();
        $this->givenResponseFromServer('');

        $results = $client->batch()
            ->notify('notify_sum', [1, 2, 4])
            ->notify('notify_hello', [2])
            ->send();

        $this->assertRequestWasSentByTransport();
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertNull($results[0]);
        $this->assertNull($results[1]);
    }

    private function createProcessingClient(): ProcessingClient
    {
        $idGenerator = new SequentialIntegerIdGenerator();
        $requestObjectFactory = new RequestObjectFactory($idGenerator);
        $serializer = new JsonObjectSerializer();
        $validator = new ExceptionalResponseValidator();
        $contextGenerator = new ContextGenerator();
        $caller = new Caller($serializer, $contextGenerator, $this->transport, $validator);

        return new ProcessingClient($requestObjectFactory, $caller);
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
