<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Bridge\Symfony\Serialization;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Bridge\Symfony\Serialization\SymfonySerializerAdapter;
use Strider2038\JsonRpcClient\Request\RequestObjectInterface;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SymfonySerializerAdapterTest extends TestCase
{
    private const CONTEXT = ['context'];
    private const SERIALIZED_RESPONSE = 'serializedResponse';

    /** @var SerializerInterface */
    private $serializer;

    protected function setUp(): void
    {
        $this->serializer = \Phake::mock(SerializerInterface::class);
    }

    /** @test */
    public function serialize_requestObject_serializedString(): void
    {
        $adapter = new SymfonySerializerAdapter($this->serializer);
        $request = \Phake::mock(RequestObjectInterface::class);
        $expectedSerializedRequest = $this->givenSerializedRequest();

        $serializedRequest = $adapter->serialize($request);

        $this->assertRequestWasSerialized($request);
        $this->assertSame($expectedSerializedRequest, $serializedRequest);
    }

    /** @test */
    public function deserialize_singeResponse_deserializedResponseReturned(): void
    {
        $adapter = new SymfonySerializerAdapter($this->serializer);
        $expectedResponse = $this->givenDeserializedResponse();

        $response = $adapter->deserialize(self::SERIALIZED_RESPONSE, self::CONTEXT);

        $this->assertSame($expectedResponse, $response);
        $this->assertResponseWasDeserialized();
    }

    private function assertRequestWasSerialized($request): void
    {
        \Phake::verify($this->serializer)
            ->serialize($request, 'json');
    }

    private function givenSerializedRequest(): string
    {
        $serializedRequest = 'serializedRequest';

        \Phake::when($this->serializer)
            ->serialize(\Phake::anyParameters())
            ->thenReturn($serializedRequest);

        return $serializedRequest;
    }

    private function assertResponseWasDeserialized(): void
    {
        \Phake::verify($this->serializer)
            ->deserialize(self::SERIALIZED_RESPONSE, ResponseObjectInterface::class, 'json', self::CONTEXT);
    }

    private function givenDeserializedResponse(): ResponseObjectInterface
    {
        $response = \Phake::mock(ResponseObjectInterface::class);

        \Phake::when($this->serializer)
            ->deserialize(\Phake::anyParameters())
            ->thenReturn($response);

        return $response;
    }
}
