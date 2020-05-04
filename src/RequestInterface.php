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

use Strider2038\JsonRpcClient\Exception\ErrorResponseException;
use Strider2038\JsonRpcClient\Exception\JsonRpcClientExceptionInterface;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface RequestInterface
{
    /**
     * Calls remote procedure with given parameters. If response processing is enabled, then procedure result will
     * be returned (payload of response) or exception will be thrown if server returns error. If response processing
     * is disabled, then @see ResponseObjectInterface will be returned.
     *
     * @param array|object|null $params
     *
     * @throws JsonRpcClientExceptionInterface on any errors
     * @throws ErrorResponseException          on server errors (if response processing is enabled)
     *
     * @return array|object|null
     */
    public function call(string $method, $params = null);

    /**
     * Calls remote procedure with given parameters. No result is expected.
     *
     * @param array|object|null $params
     *
     * @throws JsonRpcClientExceptionInterface
     */
    public function notify(string $method, $params = null);
}
