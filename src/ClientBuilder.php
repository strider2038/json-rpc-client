<?php
/*
 * This file is part of json-rpc-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient;

use Ramsey\Uuid\Uuid;
use Strider2038\JsonRpcClient\Request\IdGeneratorInterface;
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;
use Strider2038\JsonRpcClient\Request\SequentialIntegerIdGenerator;
use Strider2038\JsonRpcClient\Request\UuidGenerator;
use Strider2038\JsonRpcClient\Response\ExceptionalResponseValidator;
use Strider2038\JsonRpcClient\Response\NullResponseValidator;
use Strider2038\JsonRpcClient\Serialization\ContextGenerator;
use Strider2038\JsonRpcClient\Serialization\JsonObjectSerializer;
use Strider2038\JsonRpcClient\Serialization\MessageSerializerInterface;
use Strider2038\JsonRpcClient\Service\Caller;
use Strider2038\JsonRpcClient\Service\ProcessingClient;
use Strider2038\JsonRpcClient\Service\RawClient;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

/**
 * @experimental API may be changed
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientBuilder
{
    /**
     * If enabled then HighLevelClient will be returned with response unpacking.
     * If disabled then LowLevelClient will be returned, that will return ResponseObjectInterface
     * for each request.
     *
     * @var bool
     */
    private $enableResponseProcessing = true;

    /** @var TransportInterface */
    private $transport;

    /** @var MessageSerializerInterface */
    private $serializer;

    /** @var IdGeneratorInterface */
    private $idGenerator;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
        $this->serializer = new JsonObjectSerializer();

        if (class_exists(Uuid::class)) {
            $this->idGenerator = new UuidGenerator();
        } else {
            $this->idGenerator = new SequentialIntegerIdGenerator();
        }
    }

    public function setSerializer(MessageSerializerInterface $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }

    public function setIdGenerator(IdGeneratorInterface $idGenerator): self
    {
        $this->idGenerator = $idGenerator;

        return $this;
    }

    /**
     * If response processing is disabled then LowLevelClient will be constructed.
     *
     * @return $this
     */
    public function disableResponseProcessing(): self
    {
        $this->enableResponseProcessing = false;

        return $this;
    }

    public function getClient(): ClientInterface
    {
        $requestObjectFactory = $this->createRequestObjectFactory();
        $contextGenerator = new ContextGenerator();

        if ($this->enableResponseProcessing) {
            $caller = new Caller($this->serializer, $contextGenerator, $this->transport, new ExceptionalResponseValidator());
            $client = new ProcessingClient($requestObjectFactory, $caller);
        } else {
            $caller = new Caller($this->serializer, $contextGenerator, $this->transport, new NullResponseValidator());
            $client = new RawClient($requestObjectFactory, $caller);
        }

        return $client;
    }

    private function createRequestObjectFactory(): RequestObjectFactory
    {
        return new RequestObjectFactory($this->idGenerator);
    }
}
