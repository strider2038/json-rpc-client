<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Bridge\Symfony\DependencyInjection\Factory\SerializerFactory;
use Strider2038\JsonRpcClient\Bridge\Symfony\Serialization\SymfonySerializerAdapter;
use Strider2038\JsonRpcClient\ClientBuilder;
use Strider2038\JsonRpcClient\ClientInterface;
use Strider2038\JsonRpcClient\Request\SequentialIntegerIdGenerator;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Strider2038\JsonRpcClient\Tests\Resources\Normalizer\ComplexObjectNormalizer;
use Strider2038\JsonRpcClient\Tests\Resources\Object\ComplexObject;
use Strider2038\JsonRpcClient\Tests\Resources\Object\CreateProductRequest;
use Strider2038\JsonRpcClient\Tests\Resources\Object\CreateProductResponse;
use Strider2038\JsonRpcClient\Tests\Resources\Object\Image;
use Strider2038\JsonRpcClient\Tests\Resources\Object\Violation;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SymfonyClientTest extends TestCase
{
    private const IMAGE_FILENAME = 'image.jpeg';
    private const IMAGE_SIZE = 123456;

    private const CREATE_PRODUCT_REQUEST = '
    {
        "jsonrpc": "2.0",
        "method": "createProduct", 
        "params": {
            "name": "New product",
            "productionDate": "2020-03-09T12:30:00+00:00",
            "price": 1000,
            "images": [
                {
                    "filename": "image.jpeg",
                    "size": 123456
                }
            ]
        },
        "id": 1
    }';

    private const CREATE_PRODUCT_RESPONSE = '
    {
        "jsonrpc": "2.0", 
        "result": {
            "id": 101,
            "name": "New product",
            "productionDate": "2020-03-09T12:30:00",
            "price": 1000,
            "images": [
                {
                    "filename": "image.jpeg",
                    "size": 123456
                }
            ]
        }, 
        "id": 1
    }';

    private const COMPLEX_OBJECT_REQUEST = '
    {
        "jsonrpc": "2.0",
        "method": "sendComplexObject", 
        "params": {
            "id": 123,
            "name": "name"
        },
        "id": 1
    }';

    private const COMPLEX_OBJECT_RESPONSE = '
    {
        "jsonrpc": "2.0", 
        "result": {
            "id": 123,
            "name": "name"
        }, 
        "id": 1
    }';

    private const VIOLATION_RESPONSE = '
    {
        "jsonrpc": "2.0", 
        "error": {
            "code": -32602,
            "message": "Invalid params",
            "data": [
                {
                    "propertyPath": "name",
                    "message": "Invalid name"
                }
            ]
        }, 
        "id": 1
    }';

    /** @var TransportInterface */
    private $transport;

    protected function setUp(): void
    {
        $this->transport = \Phake::mock(TransportInterface::class);
    }

    /** @test */
    public function singleRequest_objectRequestAndServerReturnsPositiveResult_objectResultReturned(): void
    {
        $client = $this->createClient();
        $this->givenResponseFromServer(self::CREATE_PRODUCT_RESPONSE);
        $request = $this->givenCreateProductRequest();

        /** @var ResponseObjectInterface $response */
        $response = $client->call('createProduct', $request);
        /** @var CreateProductResponse $result */
        $result = $response->getResult();

        $this->assertInstanceOf(ResponseObjectInterface::class, $response);
        $this->assertInstanceOf(CreateProductResponse::class, $result);
        $this->assertFalse($response->hasError());
        $this->assertRequestWasSentByTransport(self::CREATE_PRODUCT_REQUEST);
        $this->assertSame(101, $result->id);
        $this->assertSame($request->name, $result->name);
        $this->assertSame($request->productionDate->format(DATE_ATOM), $result->productionDate->format(DATE_ATOM));
        $this->assertSame($request->price, $result->price);
        $this->assertCount(1, $result->images);
        $this->assertSame(self::IMAGE_FILENAME, $result->images[0]->filename);
        $this->assertSame(self::IMAGE_SIZE, $result->images[0]->size);
    }

    /** @test */
    public function singleRequest_objectRequestAndServerReturnsErrorResult_violationReturned(): void
    {
        $client = $this->createClient();
        $this->givenResponseFromServer(self::VIOLATION_RESPONSE);
        $request = $this->givenCreateProductRequest();

        /** @var ResponseObjectInterface $response */
        $response = $client->call('createProduct', $request);
        /** @var Violation[] $violations */
        $violations = $response->getError()->getData();

        $this->assertInstanceOf(ResponseObjectInterface::class, $response);
        $this->assertNull($response->getResult());
        $this->assertTrue($response->hasError());
        $this->assertRequestWasSentByTransport(self::CREATE_PRODUCT_REQUEST);
        $this->assertSame(-32602, $response->getError()->getCode());
        $this->assertSame('Invalid params', $response->getError()->getMessage());
        $this->assertCount(1, $violations);
        $this->assertSame('name', $violations[0]->propertyPath);
        $this->assertSame('Invalid name', $violations[0]->message);
    }

    /** @test */
    public function singleRequest_complexObjectWithCustomNormalizer_complexObjectResultReturned(): void
    {
        $client = $this->createClient();
        $this->givenResponseFromServer(self::COMPLEX_OBJECT_RESPONSE);
        $request = new ComplexObject(123, 'name');

        /** @var ResponseObjectInterface $response */
        $response = $client->call('sendComplexObject', $request);
        /** @var ComplexObject $result */
        $result = $response->getResult();

        $this->assertInstanceOf(ResponseObjectInterface::class, $response);
        $this->assertInstanceOf(ComplexObject::class, $result);
        $this->assertFalse($response->hasError());
        $this->assertRequestWasSentByTransport(self::COMPLEX_OBJECT_REQUEST);
        $this->assertSame(123, $result->getId());
        $this->assertSame('name', $result->getName());
        $this->assertSame(['meta' => 'data'], $result->getMeta());
    }

    private function createClient(): ClientInterface
    {
        $clientBuilder = new ClientBuilder($this->transport);
        $clientBuilder = $clientBuilder->disableResponseProcessing();
        $clientBuilder = $clientBuilder->setIdGenerator(new SequentialIntegerIdGenerator());
        $clientBuilder->setResultTypesByMethods([
            'createProduct'     => CreateProductResponse::class,
            'sendComplexObject' => ComplexObject::class,
        ]);
        $clientBuilder->setErrorType(Violation::class.'[]');

        $serializer = SerializerFactory::createSerializer([
            new ComplexObjectNormalizer(),
        ]);
        $serializerAdapter = new SymfonySerializerAdapter($serializer);

        $clientBuilder->setSerializer($serializerAdapter);

        return $clientBuilder->getClient();
    }

    private function assertRequestWasSentByTransport(string $request): void
    {
        \Phake::verify($this->transport)
            ->send(json_encode(json_decode($request, false)));
    }

    private function givenResponseFromServer(string $response): void
    {
        \Phake::when($this->transport)
            ->send(\Phake::anyParameters())
            ->thenReturn($response);
    }

    private function givenCreateProductRequest(): CreateProductRequest
    {
        $request = new CreateProductRequest();
        $request->name = 'New product';
        $request->productionDate = new \DateTimeImmutable('2020-03-09T12:30:00');
        $request->price = 1000;
        $image = new Image();
        $image->filename = self::IMAGE_FILENAME;
        $image->size = self::IMAGE_SIZE;
        $request->images = [$image];

        return $request;
    }
}
