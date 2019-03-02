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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class BatchRequester implements BatchRequestInterface
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

    public function call(string $method, $params): BatchRequestInterface
    {
    }

    public function notify(string $method, $params): BatchRequestInterface
    {
    }

    public function send()
    {
    }
}