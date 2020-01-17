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
use Ramsey\Uuid\Uuid;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;
use Strider2038\JsonRpcClient\Request\SequentialIntegerIdGenerator;
use Strider2038\JsonRpcClient\Request\UuidGenerator;
use Strider2038\JsonRpcClient\Response\ExceptionalResponseValidator;
use Strider2038\JsonRpcClient\Serialization\JsonObjectSerializer;
use Strider2038\JsonRpcClient\Service\Caller;
use Strider2038\JsonRpcClient\Service\HighLevelClient;
use Strider2038\JsonRpcClient\Transport\TransportFactory;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

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
        $requestObjectFactory = $this->createRequestObjectFactory();
        $transport = $this->transportFactory->createTransport($connection, GeneralOptions::createFromArray($options));
        $caller = $this->createCaller($transport);

        return new HighLevelClient($requestObjectFactory, $caller);
    }

    private function createRequestObjectFactory(): RequestObjectFactory
    {
        if (class_exists(Uuid::class)) {
            $idGenerator = new UuidGenerator();
        } else {
            $idGenerator = new SequentialIntegerIdGenerator();
        }

        return new RequestObjectFactory($idGenerator);
    }

    private function createCaller(TransportInterface $transport): Caller
    {
        $serializer = new JsonObjectSerializer();
        $validator = new ExceptionalResponseValidator();

        return new Caller($serializer, $transport, $validator);
    }
}
