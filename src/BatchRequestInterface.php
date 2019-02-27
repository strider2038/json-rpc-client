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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface BatchRequestInterface extends RequestInterface
{
    /**
     * Adds remote procedure call to delayed queue in batch request. Method returns fluent batch
     * request object. Batch request is executed by method send().
     *
     * @param string $method
     * @param $params
     * @return BatchRequestInterface
     */
    public function call(string $method, $params): BatchRequestInterface;

    /**
     * Adds remote procedure call to delayed queue in batch request. Method returns fluent batch
     * request object. Batch request is executed by method send().
     *
     * @param string $method
     * @param $params
     * @return BatchRequestInterface
     */
    public function notify(string $method, $params): BatchRequestInterface;

    /**
     * Executes batch request and returns properly ordered results in array.
     *
     * @return array
     */
    public function send();
}
