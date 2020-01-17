<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient;

use Strider2038\JsonRpcClient\Exception\JsonRpcClientException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface RequestInterface
{
    /**
     * Calls remote procedure with given parameters. Procedure result (for high level client)
     * or server response (for low level client) is returned.
     *
     * @param array|object|null $params
     *
     * @throws JsonRpcClientException
     *
     * @return array|object|null
     */
    public function call(string $method, $params = null);

    /**
     * Calls remote procedure with given parameters. No result is expected.
     *
     * @param array|object|null $params
     *
     * @throws JsonRpcClientException
     */
    public function notify(string $method, $params = null);
}
