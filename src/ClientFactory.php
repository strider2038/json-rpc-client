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
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Configuration\SerializationOptions;
use Strider2038\JsonRpcClient\Serialization\JsonArraySerializer;
use Strider2038\JsonRpcClient\Transport\TransportFactory;

/**
 * @experimental API may be changed
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientFactory
{
    /** @var TransportFactory */
    private $transportFactory;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->transportFactory = new TransportFactory($logger);
    }

    public function createClient(string $connection, array $options = []): ClientInterface
    {
        $generalOptions = GeneralOptions::createFromArray($options);
        $transport = $this->transportFactory->createTransport($connection, $generalOptions);
        $clientBuilder = new ClientBuilder($transport);

        $serializationOptions = $generalOptions->getSerializationOptions();

        if (SerializationOptions::ARRAY_SERIALIZER === $serializationOptions->getSerializer()) {
            $clientBuilder->setSerializer(new JsonArraySerializer());
        }

        return $clientBuilder->getClient();
    }
}
