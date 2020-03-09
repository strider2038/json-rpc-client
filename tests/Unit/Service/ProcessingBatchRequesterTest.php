<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Exception\NoResponseReceivedException;
use Strider2038\JsonRpcClient\Request\RequestObject;
use Strider2038\JsonRpcClient\Response\ResponseObject;
use Strider2038\JsonRpcClient\Service\ProcessingBatchRequester;
use Strider2038\JsonRpcClient\Tests\TestCase\ClientTestCaseTrait;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ProcessingBatchRequesterTest extends TestCase
{
    use ClientTestCaseTrait;

    private const METHOD = 'method';
    private const PARAMS = ['params'];

    protected function setUp(): void
    {
        $this->setUpClientTestCase();
    }

    /** @test */
    public function send_multipleRequests_properlyOrderedResponsesReturned(): void
    {
        $this->givenMultipleRequestsInQueueWithIds(1, 2, 3);
        $this->givenMultipleResponsesWithIds(3, 1, 2);
        $requester = new ProcessingBatchRequester($this->requestObjectFactory, $this->caller);
        $requester->call(self::METHOD, self::PARAMS);
        $requester->call(self::METHOD, self::PARAMS);
        $requester->call(self::METHOD, self::PARAMS);

        $results = $requester->send();

        $this->assertResultSequence($results, 1, 2, 3);
    }

    /** @test */
    public function send_multipleRequestsAndNotifications_properlyOrderedResponsesReturned(): void
    {
        $this->givenMultipleRequestsInQueueWithIds(2, null, 1);
        $this->givenMultipleResponsesWithIds(1, 2, null);
        $requester = new ProcessingBatchRequester($this->requestObjectFactory, $this->caller);
        $requester->call(self::METHOD, self::PARAMS);
        $requester->call(self::METHOD, self::PARAMS);
        $requester->call(self::METHOD, self::PARAMS);

        $results = $requester->send();

        $this->assertResultSequence($results, 2, null, 1);
    }

    /** @test */
    public function send_requestWithoutResponse_exceptionThrown(): void
    {
        $this->givenMultipleRequestsInQueueWithIds(1, 2, 3);
        $this->givenMultipleResponsesWithIds(null, 1, 2);
        $requester = new ProcessingBatchRequester($this->requestObjectFactory, $this->caller);
        $requester->call(self::METHOD, self::PARAMS);
        $requester->call(self::METHOD, self::PARAMS);
        $requester->call(self::METHOD, self::PARAMS);

        $this->expectException(NoResponseReceivedException::class);

        $requester->send();
    }

    private function givenMultipleRequestsInQueueWithIds(?int ...$ids): void
    {
        $mock = \Phake::when($this->requestObjectFactory)->createRequest(\Phake::anyParameters());

        foreach ($ids as $id) {
            $mock = $mock->thenReturn($this->givenRequestObjectWithId($id));
        }
    }

    private function givenRequestObjectWithId(?int $id): RequestObject
    {
        $requestObject = new RequestObject(self::METHOD, self::PARAMS);
        $requestObject->id = $id;

        return $requestObject;
    }

    private function givenMultipleResponsesWithIds(?int ...$ids): void
    {
        $responses = [];

        foreach ($ids as $id) {
            $responses[] = $this->givenResponseObjectWithId($id);
        }

        \Phake::when($this->caller)
            ->call(\Phake::anyParameters())
            ->thenReturn($responses);
    }

    private function givenResponseObjectWithId(?int $id): ResponseObject
    {
        $responseObject = new ResponseObject();
        $responseObject->id = $id;
        $responseObject->result = $id;

        return $responseObject;
    }

    private function assertResultSequence(array $results, ?int ...$ids): void
    {
        $this->assertCount(count($ids), $results);

        foreach ($ids as $index => $id) {
            $this->assertSame($id, $results[$index]);
        }
    }
}
