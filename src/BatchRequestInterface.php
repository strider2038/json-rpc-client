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
interface BatchRequestInterface extends RequestInterface
{
    /**
     * Adds remote procedure call to the delayed queue in batch request. Method returns fluent batch
     * request object. Batch request is executed by method send().
     *
     * @param string            $method
     * @param array|object|null $params
     *
     * @throws JsonRpcClientException
     *
     * @return BatchRequestInterface
     */
    public function call(string $method, $params = null): self;

    /**
     * Adds remote procedure call to the delayed queue in batch request. Method returns fluent batch
     * request object. Batch request is executed by method send().
     *
     * @param string            $method
     * @param array|object|null $params
     *
     * @throws JsonRpcClientException
     *
     * @return BatchRequestInterface
     */
    public function notify(string $method, $params = null): self;

    /**
     * Executes batch request and returns unsorted responses (for low level client)
     * or properly ordered results in array (for high level client).
     *
     * @throws JsonRpcClientException
     *
     * @return array
     */
    public function send(): array;
}
