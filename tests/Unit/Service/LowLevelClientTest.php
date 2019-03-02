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
use Strider2038\JsonRpcClient\Request\NotificationObject;
use Strider2038\JsonRpcClient\Request\RequestObject;
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;
use Strider2038\JsonRpcClient\Request\RequestObjectInterface;
use Strider2038\JsonRpcClient\Service\BatchRequester;
use Strider2038\JsonRpcClient\Service\Caller;
use Strider2038\JsonRpcClient\Service\LowLevelClient;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class LowLevelClientTest extends TestCase
{
    private const METHOD = 'method';
    private const PARAMS = ['params'];

    /** @var RequestObjectFactory */
    private $requestObjectFactory;

    /** @var Caller */
    private $caller;

    protected function setUp(): void
    {
        $this->requestObjectFactory = \Phake::mock(RequestObjectFactory::class);
        $this->caller = \Phake::mock(Caller::class);
    }

    /** @test */
    public function batch_noParameters_batchRequesterCreatedAndReturned(): void
    {
        $client = $this->createClientService();

        $requester = $client->batch();

        $this->assertInstanceOf(BatchRequester::class, $requester);
    }

    /** @test */
    public function call_methodAndParams_requestCreatedAndResultReturnedFromCaller(): void
    {
        $client = $this->createClientService();
        $requestObject = $this->givenCreatedRequestObject();
        $expectedResult = $this->givenResultReturnedByCaller();

        $result = $client->call(self::METHOD, self::PARAMS);

        $this->assertRequestObjectCreatedWithExpectedMethodAndParams();
        $this->assertRemoteProcedureWasCalledWithRequestObject($requestObject);
        $this->assertSame($expectedResult, $result);
    }

    /** @test */
    public function notify_methodAndParams_notificationCreatedAndSendToCaller(): void
    {
        $client = $this->createClientService();
        $requestObject = $this->givenCreatedNotificationObject();

        $client->notify(self::METHOD, self::PARAMS);

        $this->assertNotificationObjectCreatedWithExpectedMethodAndParams();
        $this->assertRemoteProcedureWasCalledWithRequestObject($requestObject);
    }

    private function assertRequestObjectCreatedWithExpectedMethodAndParams(): void
    {
        \Phake::verify($this->requestObjectFactory)
            ->createRequest(self::METHOD, self::PARAMS);
    }

    private function assertNotificationObjectCreatedWithExpectedMethodAndParams(): void
    {
        \Phake::verify($this->requestObjectFactory)
            ->createNotification(self::METHOD, self::PARAMS);
    }

    private function assertRemoteProcedureWasCalledWithRequestObject(RequestObjectInterface $requestObject): void
    {
        \Phake::verify($this->caller)
            ->call($requestObject);
    }

    private function givenCreatedRequestObject(): RequestObjectInterface
    {
        $requestObject = \Phake::mock(RequestObject::class);

        \Phake::when($this->requestObjectFactory)
            ->createRequest(\Phake::anyParameters())
            ->thenReturn($requestObject);

        return $requestObject;
    }

    private function givenCreatedNotificationObject(): RequestObjectInterface
    {
        $requestObject = \Phake::mock(NotificationObject::class);

        \Phake::when($this->requestObjectFactory)
            ->createNotification(\Phake::anyParameters())
            ->thenReturn($requestObject);

        return $requestObject;
    }

    private function givenResultReturnedByCaller(): \stdClass
    {
        $result = new \stdClass();

        \Phake::when($this->caller)
            ->call(\Phake::anyParameters())
            ->thenReturn($result);

        return $result;
    }

    private function createClientService(): LowLevelClient
    {
        $client = new LowLevelClient($this->requestObjectFactory, $this->caller);
        return $client;
    }
}
