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
use Strider2038\JsonRpcClient\Exception\JsonRpcClientException;
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class LowLevelClient implements ClientInterface
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
        return new LowLevelBatchRequester($this->requestObjectFactory, $this->caller);
    }

    /**
     * Calls remote procedure with given parameters. Server response is returned.
     *
     * @param string $method
     * @param $params
     * @return ResponseObjectInterface
     * @throws JsonRpcClientException
     */
    public function call(string $method, $params)
    {
        $requestObject = $this->requestObjectFactory->createRequest($method, $params);

        return $this->caller->call($requestObject);
    }

    /**
     * @param string $method
     * @param $params
     * @throws JsonRpcClientException
     */
    public function notify(string $method, $params): void
    {
        $notificationObject = $this->requestObjectFactory->createNotification($method, $params);

        $this->caller->call($notificationObject);
    }
}
