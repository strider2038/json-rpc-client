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
     * If enabled then ProcessingClient will be returned with response unpacking.
     * If disabled then RawClient will be returned, that will return ResponseObjectInterface
     * for each request.
     */
    private bool $enableResponseProcessing = true;

    private TransportInterface $transport;

    private MessageSerializerInterface $serializer;

    private IdGeneratorInterface $idGenerator;

    /** @var string[] */
    private array $resultTypesByMethods = [];

    private ?string $defaultErrorType = null;

    /** @var string[] */
    private array $errorTypesByMethods = [];

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
     * If response processing is enabled then ProcessingClient will be constructed.
     *
     * @return $this
     */
    public function enableResponseProcessing(): self
    {
        $this->enableResponseProcessing = true;

        return $this;
    }

    /**
     * If response processing is disabled then RawClient will be constructed.
     *
     * @return $this
     */
    public function disableResponseProcessing(): self
    {
        $this->enableResponseProcessing = false;

        return $this;
    }

    public function setResultTypesByMethods(array $resultTypesByMethods): self
    {
        $this->resultTypesByMethods = $resultTypesByMethods;

        return $this;
    }

    public function setDefaultErrorType(?string $defaultErrorType): self
    {
        $this->defaultErrorType = $defaultErrorType;

        return $this;
    }

    public function setErrorTypesByMethods(array $errorTypesByMethods): void
    {
        $this->errorTypesByMethods = $errorTypesByMethods;
    }

    public function getClient(): ClientInterface
    {
        $requestObjectFactory = $this->createRequestObjectFactory();
        $contextGenerator = new ContextGenerator($this->resultTypesByMethods, $this->defaultErrorType, $this->errorTypesByMethods);

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
