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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientService implements ClientInterface
{
    public function batch(): BatchRequestInterface
    {
        // TODO: Implement batch() method.
    }

    public function call(string $method, $params)
    {
        // TODO: Implement call() method.
    }

    public function notify(string $method, $params)
    {
        // TODO: Implement notify() method.
    }
}
