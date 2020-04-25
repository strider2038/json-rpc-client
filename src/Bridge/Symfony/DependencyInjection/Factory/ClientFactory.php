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
use Strider2038\JsonRpcClient\ClientBuilder;
use Strider2038\JsonRpcClient\ClientFactoryInterface;
use Strider2038\JsonRpcClient\ClientInterface;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Configuration\SerializationOptions;
use Strider2038\JsonRpcClient\Serialization\JsonArraySerializer;
use Strider2038\JsonRpcClient\Transport\MultiTransportFactory;
use Strider2038\JsonRpcClient\Transport\TransportFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientFactory implements ClientFactoryInterface
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var TransportFactoryInterface */
    private $transportFactory;

    public function __construct(SerializerInterface $serializer, LoggerInterface $logger = null)
    {
        $this->serializer = $serializer;
        $this->transportFactory = new MultiTransportFactory($logger);
    }

    public function createClient(string $url, array $options = []): ClientInterface
    {
        $generalOptions = GeneralOptions::createFromArray($options);
        $transport = $this->transportFactory->createTransport($url, $generalOptions);
        $clientBuilder = new ClientBuilder($transport);

        $serializationOptions = $generalOptions->getSerializationOptions();

        $clientBuilder->setResultTypesByMethods($serializationOptions->getResultTypesByMethods());
        $clientBuilder->setDefaultErrorType($serializationOptions->getDefaultErrorType());

        $serializerType = $serializationOptions->getSerializerType();

        if (SerializationOptions::ARRAY_SERIALIZER === $serializerType) {
            $clientBuilder->setSerializer(new JsonArraySerializer());
        } elseif (SerializationOptions::SYMFONY_SERIALIZER === $serializerType) {
            $clientBuilder->setSerializer(new SymfonySerializerAdapter($this->serializer));
        }

        return $clientBuilder->getClient();
    }
}
