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
use Strider2038\JsonRpcClient\Service\RawClient;
use Strider2038\JsonRpcClient\Tests\TestCase\ClientTestCaseTrait;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RawClientTest extends TestCase
{
    use ClientTestCaseTrait;

    private const METHOD = 'method';
    private const PARAMS = ['params'];

    protected function setUp(): void
    {
        $this->setUpClientTestCase();
    }

    /** @test */
    public function batch_noParameters_batchRequesterCreatedAndReturned(): void
    {
        $client = $this->createClient();

        $requester = $client->batch();

        $this->assertInstanceOf(RawBatchRequester::class, $requester);
    }

    /** @test */
    public function call_methodAndParams_requestCreatedAndResultReturnedFromCaller(): void
    {
        $client = $this->createClient();
        $requestObject = $this->givenCreatedRequestObject();
        $expectedResult = $this->givenResultReturnedByCaller();

        $result = $client->call(self::METHOD, self::PARAMS);

        $this->assertRequestObjectCreatedWithExpectedMethodAndParams(self::METHOD, self::PARAMS);
        $this->assertRemoteProcedureWasCalledWithRequestObject($requestObject);
        $this->assertSame($expectedResult, $result);
    }

    /** @test */
    public function notify_methodAndParams_notificationCreatedAndSendToCaller(): void
    {
        $client = $this->createClient();
        $requestObject = $this->givenCreatedNotificationObject();

        $client->notify(self::METHOD, self::PARAMS);

        $this->assertNotificationObjectCreatedWithExpectedMethodAndParams(self::METHOD, self::PARAMS);
        $this->assertRemoteProcedureWasCalledWithRequestObject($requestObject);
    }

    private function createClient(): RawClient
    {
        return new RawClient($this->requestObjectFactory, $this->caller);
    }
}
