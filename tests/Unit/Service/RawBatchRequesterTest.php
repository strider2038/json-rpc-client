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
use Strider2038\JsonRpcClient\Service\RawBatchRequester;
use Strider2038\JsonRpcClient\Tests\TestCase\ClientTestCaseTrait;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RawBatchRequesterTest extends TestCase
{
    use ClientTestCaseTrait;

    private const METHOD = 'method';
    private const PARAMS = ['params'];

    protected function setUp(): void
    {
        $this->setUpClientTestCase();
    }

    /** @test */
    public function send_noRequestsInQueue_emptyArrayReturned(): void
    {
        $requester = $this->createLowLevelBatchRequester();

        $responses = $requester->send();

        $this->assertIsArray($responses);
        $this->assertCount(0, $responses);
    }

    /** @test */
    public function call_requestObjectInQueueAndSingleResponseReturnedFromCaller_responseReturnedInArray(): void
    {
        $requester = $this->createLowLevelBatchRequester();
        $requestObject = $this->givenCreatedRequestObject();
        $responseObject = $this->givenResponseReturnedByCaller();
        $requester->call(self::METHOD, self::PARAMS);

        $responses = $requester->send();

        $this->assertIsArray($responses);
        $this->assertCount(1, $responses);
        $this->assertContains($responseObject, $responses);
        $this->assertRequestObjectCreatedWithExpectedMethodAndParams(self::METHOD, self::PARAMS);
        $this->assertRemoteProcedureWasCalledWithRequestObjectInArray($requestObject);
    }

    /** @test */
    public function send_requestObjectInQueueAndArrayResponseReturnedFromCaller_responseReturnedInArray(): void
    {
        $requester = $this->createLowLevelBatchRequester();
        $requestObject = $this->givenCreatedRequestObject();
        $responseObject = $this->givenResponseInArrayReturnedByCaller();
        $requester->call(self::METHOD, self::PARAMS);

        $responses = $requester->send();

        $this->assertIsArray($responses);
        $this->assertCount(1, $responses);
        $this->assertContains($responseObject, $responses);
        $this->assertRequestObjectCreatedWithExpectedMethodAndParams(self::METHOD, self::PARAMS);
        $this->assertRemoteProcedureWasCalledWithRequestObjectInArray($requestObject);
    }

    /** @test */
    public function send_notificationObjectInQueueAndEmptyResponseReturnedFromCaller_arrayIsEmpty(): void
    {
        $requester = $this->createLowLevelBatchRequester();
        $requestObject = $this->givenCreatedNotificationObject();
        $this->givenCallerReturnsNull();
        $requester->notify(self::METHOD, self::PARAMS);

        $responses = $requester->send();

        $this->assertIsArray($responses);
        $this->assertCount(0, $responses);
        $this->assertNotificationObjectCreatedWithExpectedMethodAndParams(self::METHOD, self::PARAMS);
        $this->assertRemoteProcedureWasCalledWithRequestObjectInArray($requestObject);
    }

    private function createLowLevelBatchRequester(): RawBatchRequester
    {
        return new RawBatchRequester($this->requestObjectFactory, $this->caller);
    }
}
