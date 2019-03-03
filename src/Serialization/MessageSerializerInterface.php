<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Serialization;

use Strider2038\JsonRpcClient\Request\RequestObjectInterface;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface MessageSerializerInterface
{
    /**
     * @param RequestObjectInterface|RequestObjectInterface[] $request
     * @return string
     */
    public function serialize($request): string;

    /**
     * @param string $response
     * @return ResponseObjectInterface|ResponseObjectInterface[]|null
     */
    public function deserialize(string $response);
}
