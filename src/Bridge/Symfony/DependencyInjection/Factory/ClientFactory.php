<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Bridge\Symfony\DependencyInjection\Factory;

use Psr\Log\LoggerInterface;
use Strider2038\JsonRpcClient\Bridge\Symfony\Serialization\SymfonySerializerAdapter;
use Strider2038\JsonRpcClient\ClientFactory as BaseClientFactory;
use Strider2038\JsonRpcClient\Configuration\SerializationOptions;
use Strider2038\JsonRpcClient\Serialization\JsonArraySerializer;
use Strider2038\JsonRpcClient\Serialization\JsonObjectSerializer;
use Strider2038\JsonRpcClient\Serialization\MessageSerializerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientFactory extends BaseClientFactory
{
    /** @var MessageSerializerInterface[] */
    private $serializers;

    public function __construct(SerializerInterface $serializer, LoggerInterface $logger = null)
    {
        parent::__construct($logger);

        $this->serializers = [
            SerializationOptions::OBJECT_SERIALIZER  => new JsonObjectSerializer(),
            SerializationOptions::ARRAY_SERIALIZER   => new JsonArraySerializer(),
            SerializationOptions::SYMFONY_SERIALIZER => new SymfonySerializerAdapter($serializer),
        ];
    }

    protected function createSerializer(string $serializerType): MessageSerializerInterface
    {
        return $this->serializers[$serializerType];
    }
}
