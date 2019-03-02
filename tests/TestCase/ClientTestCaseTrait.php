<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\TestCase;

use Strider2038\JsonRpcClient\Request\NotificationObject;
use Strider2038\JsonRpcClient\Request\RequestObject;
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;
use Strider2038\JsonRpcClient\Request\RequestObjectInterface;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Strider2038\JsonRpcClient\Service\Caller;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait ClientTestCaseTrait
{
    /** @var RequestObjectFactory */
    protected $requestObjectFactory;

    /** @var Caller */
    protected $caller;

    protected function setUpClientTestCase(): void
    {
        $this->requestObjectFactory = \Phake::mock(RequestObjectFactory::class);
        $this->caller = \Phake::mock(Caller::class);
    }

    protected function givenCreatedRequestObject(): RequestObjectInterface
    {
        $requestObject = \Phake::mock(RequestObject::class);

        \Phake::when($this->requestObjectFactory)
            ->createRequest(\Phake::anyParameters())
            ->thenReturn($requestObject);

        return $requestObject;
    }

    protected function assertNotificationObjectCreatedWithExpectedMethodAndParams(string $method, $params): void
    {
        \Phake::verify($this->requestObjectFactory)
            ->createNotification($method, $params);
    }

    protected function assertRemoteProcedureWasCalledWithRequestObject(RequestObjectInterface $requestObject): void
    {
        \Phake::verify($this->caller)
            ->call($requestObject);
    }

    protected function assertRemoteProcedureWasCalledWithRequestObjectInArray(RequestObjectInterface $requestObject): void
    {
        \Phake::verify($this->caller)
            ->call([$requestObject]);
    }

    protected function givenResultReturnedByCaller(): \stdClass
    {
        $result = new \stdClass();

        \Phake::when($this->caller)
            ->call(\Phake::anyParameters())
            ->thenReturn($result);

        return $result;
    }

    protected function givenResponseReturnedByCaller(): ResponseObjectInterface
    {
        $response = \Phake::mock(ResponseObjectInterface::class);

        \Phake::when($this->caller)
            ->call(\Phake::anyParameters())
            ->thenReturn($response);

        return $response;
    }

    protected function givenCallerReturnsNull(): void
    {
        \Phake::when($this->caller)
            ->call(\Phake::anyParameters())
            ->thenReturn(null);
    }

    protected function givenResponseInArrayReturnedByCaller(): ResponseObjectInterface
    {
        $response = \Phake::mock(ResponseObjectInterface::class);

        \Phake::when($this->caller)
            ->call(\Phake::anyParameters())
            ->thenReturn([$response]);

        return $response;
    }

    protected function assertRequestObjectCreatedWithExpectedMethodAndParams(string $method, $params): void
    {
        \Phake::verify($this->requestObjectFactory)
            ->createRequest($method, $params);
    }

    protected function givenCreatedNotificationObject(): RequestObjectInterface
    {
        $requestObject = \Phake::mock(NotificationObject::class);

        \Phake::when($this->requestObjectFactory)
            ->createNotification(\Phake::anyParameters())
            ->thenReturn($requestObject);

        return $requestObject;
    }
}
