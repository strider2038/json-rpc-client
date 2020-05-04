<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Service;

use Strider2038\JsonRpcClient\BatchRequestInterface;
use Strider2038\JsonRpcClient\ClientInterface;
use Strider2038\JsonRpcClient\Exception\JsonRpcClientExceptionInterface;
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;

/**
 * Can be used for high-level operations. Remote procedure results returned as response data. On server
 * errors it throws exceptions. It is recommended for use as reliable channel between client and server
 * when errors from server are not expected as normal behaviour. Advantage of this type of client over
 * raw client is automatic ordering of responses for batch requests.
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ProcessingClient implements ClientInterface
{
    /** @var RequestObjectFactory */
    private $requestObjectFactory;

    /** @var Caller */
    private $caller;

    public function __construct(RequestObjectFactory $requestObjectFactory, Caller $caller)
    {
        $this->requestObjectFactory = $requestObjectFactory;
        $this->caller = $caller;
    }

    public function batch(): BatchRequestInterface
    {
        return new ProcessingBatchRequester($this->requestObjectFactory, $this->caller);
    }

    /**
     * @param array|object|null $params
     *
     * @throws JsonRpcClientExceptionInterface
     *
     * @return array|object|null
     */
    public function call(string $method, $params = null)
    {
        $result = null;
        $requestObject = $this->requestObjectFactory->createRequest($method, $params);
        $responseObject = $this->caller->call($requestObject);

        if (null !== $responseObject) {
            $result = $responseObject->getResult();
        }

        return $result;
    }

    /**
     * @param array|object|null $params
     *
     * @throws JsonRpcClientExceptionInterface
     */
    public function notify(string $method, $params = null): void
    {
        $notificationObject = $this->requestObjectFactory->createNotification($method, $params);

        $this->caller->call($notificationObject);
    }
}
