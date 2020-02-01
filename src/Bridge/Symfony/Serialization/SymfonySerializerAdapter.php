<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Bridge\Symfony\Serialization;

use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Strider2038\JsonRpcClient\Serialization\MessageSerializerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SymfonySerializerAdapter implements MessageSerializerInterface
{
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize($request): string
    {
        return $this->serializer->serialize($request, 'json');
    }

    public function deserialize(string $response, array $context)
    {
        return $this->serializer->deserialize($response, ResponseObjectInterface::class, 'json', $context);
    }
}
