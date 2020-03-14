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
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Strider2038\JsonRpcClient\Service\ProcessingBatchRequester;
use Strider2038\JsonRpcClient\Service\ProcessingClient;
use Strider2038\JsonRpcClient\Tests\TestCase\ClientTestCaseTrait;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ProcessingClientTest extends TestCase
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

        $this->assertInstanceOf(ProcessingBatchRequester::class, $requester);
    }

    /** @test */
    public function call_methodAndParams_requestCreatedAndResultReturnedFromCaller(): void
    {
        $client = $this->createClient();
        $requestObject = $this->givenCreatedRequestObject();
        $responseObject = $this->givenResponseReturnedByCaller();
        $expectedResult = $this->givenResultInResponse($responseObject);

        $result = $client->call(self::METHOD, self::PARAMS);

        $this->assertRequestObjectCreatedWithExpectedMethodAndParams(self::METHOD, self::PARAMS);
        $this->assertRemoteProcedureWasCalledWithRequestObject($requestObject);
        $this->assertResultWasExtractedFromResponse($responseObject);
        $this->assertSame($expectedResult, $result);
    }

    /** @test */
    public function call_nullResponse_null(): void
    {
        $client = $this->createClient();
        $requestObject = $this->givenCreatedRequestObject();
        $this->givenCallerReturnsNull();

        $result = $client->call(self::METHOD, self::PARAMS);

        $this->assertRequestObjectCreatedWithExpectedMethodAndParams(self::METHOD, self::PARAMS);
        $this->assertRemoteProcedureWasCalledWithRequestObject($requestObject);
        $this->assertNull($result);
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

    private function createClient(): ProcessingClient
    {
        return new ProcessingClient($this->requestObjectFactory, $this->caller);
    }

    private function assertResultWasExtractedFromResponse(ResponseObjectInterface $responseObject): void
    {
        \Phake::verify($responseObject)
            ->getResult();
    }

    private function givenResultInResponse(ResponseObjectInterface $responseObject): string
    {
        $expectedResult = 'result';
        \Phake::when($responseObject)
            ->getResult()
            ->thenReturn($expectedResult);

        return $expectedResult;
    }
}
