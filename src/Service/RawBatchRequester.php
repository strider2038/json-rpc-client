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
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;
use Strider2038\JsonRpcClient\Request\RequestObjectInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RawBatchRequester implements BatchRequestInterface
{
    private RequestObjectFactory $requestObjectFactory;

    private Caller $caller;

    /** @var RequestObjectInterface[] */
    private array $queue = [];

    public function __construct(RequestObjectFactory $requestObjectFactory, Caller $caller)
    {
        $this->requestObjectFactory = $requestObjectFactory;
        $this->caller = $caller;
    }

    public function call(string $method, $params = null): BatchRequestInterface
    {
        $this->queue[] = $this->requestObjectFactory->createRequest($method, $params);

        return $this;
    }

    public function notify(string $method, $params = null): BatchRequestInterface
    {
        $this->queue[] = $this->requestObjectFactory->createNotification($method, $params);

        return $this;
    }

    public function send(): array
    {
        $responses = [];

        if (count($this->queue) > 0) {
            $responses = $this->caller->call($this->queue);

            if (null === $responses) {
                $responses = [];
            } elseif (!is_array($responses)) {
                $responses = [$responses];
            }
        }

        return $responses;
    }

    public function getQueue(): array
    {
        return $this->queue;
    }
}
