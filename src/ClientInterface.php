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
interface ClientInterface extends RequestInterface
{
    /**
     * Creates and returns fluent batch request object. For sending batch request use send() method.
     *
     * @return BatchRequestInterface
     */
    public function batch(): BatchRequestInterface;
}
