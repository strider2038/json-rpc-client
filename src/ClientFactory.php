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

use Psr\Log\LoggerInterface;
use Strider2038\JsonRpcClient\Bridge\Symfony\DependencyInjection\Factory\SerializerFactory;
use Strider2038\JsonRpcClient\Bridge\Symfony\Serialization\SymfonySerializerAdapter;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Configuration\SerializationOptions;
use Strider2038\JsonRpcClient\Serialization\JsonArraySerializer;
use Strider2038\JsonRpcClient\Transport\MultiTransportFactory;
use Strider2038\JsonRpcClient\Transport\TransportFactoryInterface;

/**
 * @experimental API may be changed
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientFactory implements ClientFactoryInterface
{
    /** @var TransportFactoryInterface */
    private $transportFactory;

    public function __construct(LoggerInterface $logger = null)
    {
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
            $serializer = SerializerFactory::createSerializer();
            $clientBuilder->setSerializer(new SymfonySerializerAdapter($serializer));
        }

        return $clientBuilder->getClient();
    }
}
