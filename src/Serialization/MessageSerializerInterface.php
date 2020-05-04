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

use Strider2038\JsonRpcClient\Configuration\SerializationOptions;
use Strider2038\JsonRpcClient\Request\RequestObjectInterface;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface MessageSerializerInterface
{
    /**
     * Serializes single request or a batch of requests into binary string that will be sent to server over transport.
     *
     * @param RequestObjectInterface|RequestObjectInterface[] $request
     */
    public function serialize($request): string;

    /**
     * Deserializes single response or a batch of responses into classes or objects.
     *
     * Context contains data that can be used for deserialization process:
     *
     * * $context['json_rpc']['result_types_by_methods'] - set from @see SerializationOptions
     * * $context['json_rpc']['default_error_type'] - set from @see SerializationOptions
     * * $context['json_rpc']['error_types_by_methods'] - set from @see SerializationOptions
     * * $context['json_rpc']['request'] contains @see RequestObjectInterface for singe request
     * * $context['json_rpc']['requests'] contains an array of @see RequestObjectInterface for batch request
     *
     * @return ResponseObjectInterface|ResponseObjectInterface[]|null
     */
    public function deserialize(string $response, array $context);
}
