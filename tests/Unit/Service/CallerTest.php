<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Request\RequestObjectInterface;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Strider2038\JsonRpcClient\Response\ResponseValidatorInterface;
use Strider2038\JsonRpcClient\Serialization\ContextGenerator;
use Strider2038\JsonRpcClient\Serialization\MessageSerializerInterface;
use Strider2038\JsonRpcClient\Service\Caller;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class CallerTest extends TestCase
{
    private const SERIALIZATION_CONTEXT = ['serializationContext'];

    /** @var MessageSerializerInterface */
    private $serializer;

    /** @var ContextGenerator */
    private $contextGenerator;

    /** @var TransportInterface */
    private $transport;

    /** @var ResponseValidatorInterface */
    private $validator;

    protected function setUp(): void
    {
        $this->serializer = \Phake::mock(MessageSerializerInterface::class);
        $this->contextGenerator = \Phake::mock(ContextGenerator::class);
        $this->transport = \Phake::mock(TransportInterface::class);
        $this->validator = \Phake::mock(ResponseValidatorInterface::class);
    }

    /** @test */
    public function call_singleRequest_requestSentByTransportAndProcessedResponseReturned(): void
    {
        $caller = $this->createCaller();
        $request = \Phake::mock(RequestObjectInterface::class);
        $serializedRequest = $this->givenSerializedRequest();
        $serializedResponse = $this->givenSerializedResponse();
        $serializationContext = $this->givenSerializationContext();

        $expectedResponse = $this->givenDeserializedResponse();

        $response = $caller->call($request);

        $this->assertRequestWasSerialized($request);
        $this->assertRequestWasSentByTransport($serializedRequest);
        $this->assertSerializationContextWasCreated($request);
        $this->assertResponseWasDeserializedWithContext($serializedResponse, $serializationContext);
        $this->assertResponseWasValidated($expectedResponse);
        $this->assertSame($expectedResponse, $response);
    }

    private function assertRequestWasSerialized(RequestObjectInterface $request): void
    {
        \Phake::verify($this->serializer)
            ->serialize($request);
    }

    private function assertRequestWasSentByTransport(string $serializedRequest): void
    {
        \Phake::verify($this->transport)
            ->send($serializedRequest);
    }

    private function givenSerializedRequest(): string
    {
        $serializedRequest = 'serializedRequest';

        \Phake::when($this->serializer)
            ->serialize(\Phake::anyParameters())
            ->thenReturn($serializedRequest);

        return $serializedRequest;
    }

    private function assertResponseWasDeserializedWithContext(string $serializedResponse, array $context): void
    {
        \Phake::verify($this->serializer)
            ->deserialize($serializedResponse, $context);
    }

    private function givenSerializedResponse(): string
    {
        $serializedResponse = 'serializedResponse';

        \Phake::when($this->transport)
            ->send(\Phake::anyParameters())
            ->thenReturn($serializedResponse);

        return $serializedResponse;
    }

    private function createCaller(): Caller
    {
        return new Caller($this->serializer, $this->contextGenerator, $this->transport, $this->validator);
    }

    private function assertResponseWasValidated($response): void
    {
        \Phake::verify($this->validator)
            ->validate($response);
    }

    private function givenDeserializedResponse(): ResponseObjectInterface
    {
        $response = \Phake::mock(ResponseObjectInterface::class);

        \Phake::when($this->serializer)
            ->deserialize(\Phake::anyParameters())
            ->thenReturn($response);

        return $response;
    }

    private function assertSerializationContextWasCreated($request): void
    {
        \Phake::verify($this->contextGenerator)
            ->createSerializationContext($request);
    }

    private function givenSerializationContext(): array
    {
        $serializationContext = self::SERIALIZATION_CONTEXT;
        \Phake::when($this->contextGenerator)
            ->createSerializationContext(\Phake::anyParameters())
            ->thenReturn($serializationContext);

        return $serializationContext;
    }
}
