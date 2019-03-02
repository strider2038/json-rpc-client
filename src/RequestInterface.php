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
     * Calls remote procedure with given parameters. Procedure result is returned.
     *
     * @param string $method
     * @param $params
     * @return array|object
     *
     * @throws JsonRpcClientException
     */
    public function call(string $method, $params);

    /**
     * Calls remote procedure with given parameters. No result is expected.
     *
     * @param string $method
     * @param $params
     * @return void
     *
     * @throws JsonRpcClientException
     */
    public function notify(string $method, $params);
}
