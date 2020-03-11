<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Serialization;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Exception\InvalidResponseException;
use Strider2038\JsonRpcClient\Request\NotificationObject;
use Strider2038\JsonRpcClient\Request\RequestObject;
use Strider2038\JsonRpcClient\Response\ResponseObject;
use Strider2038\JsonRpcClient\Serialization\JsonArraySerializer;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class JsonArraySerializerTest extends TestCase
{
    /** @test */
    public function serialize_singleRequest_jsonStringReturned(): void
    {
        $serializer = new JsonArraySerializer();
        $request = new RequestObject('id', 'method', ['parameterName' => 'parameterValue']);

        $serializedRequest = $serializer->serialize($request);

        $this->assertSame(
            '{"jsonrpc":"2.0","method":"method","params":{"parameterName":"parameterValue"},"id":"id"}',
            $serializedRequest
        );
    }

    /** @test */
    public function serialize_singleNotification_jsonStringReturned(): void
    {
        $serializer = new JsonArraySerializer();
        $request = new NotificationObject('method', ['parameterName' => 'parameterValue']);

        $serializedRequest = $serializer->serialize($request);

        $this->assertSame(
            '{"jsonrpc":"2.0","method":"method","params":{"parameterName":"parameterValue"}}',
            $serializedRequest
        );
    }

    /** @test */
    public function serialize_arrayWithRequest_jsonStringReturned(): void
    {
        $serializer = new JsonArraySerializer();
        $request = new RequestObject('id', 'method', ['parameterName' => 'parameterValue']);

        $serializedRequest = $serializer->serialize([$request]);

        $this->assertSame(
            '[{"jsonrpc":"2.0","method":"method","params":{"parameterName":"parameterValue"},"id":"id"}]',
            $serializedRequest
        );
    }

    /** @test */
    public function deserialize_singleSuccessfulResponse_responseWithResultReturned(): void
    {
        $serializer = new JsonArraySerializer();
        $serializedResponse = '
        {
            "jsonrpc": "2.0",
            "result": "resultValue",
            "id": "idValue"
        }
        ';

        $response = $serializer->deserialize($serializedResponse, []);

        $this->assertInstanceOf(ResponseObject::class, $response);
        $this->assertSame('2.0', $response->getProtocol());
        $this->assertSame('resultValue', $response->getResult());
        $this->assertSame('idValue', $response->getId());
        $this->assertFalse($response->hasError());
    }

    /** @test */
    public function deserialize_singleErrorResponse_responseWithErrorReturned(): void
    {
        $serializer = new JsonArraySerializer();
        $serializedResponse = '
        {
            "jsonrpc": "2.0",
            "error": {
                "code": 1,
                "message": "errorMessage",
                "data": {"errorDataKey": "errorDataValue"}
            },
            "id": "idValue"
        }
        ';

        $response = $serializer->deserialize($serializedResponse, []);

        $this->assertInstanceOf(ResponseObject::class, $response);
        $this->assertSame('2.0', $response->getProtocol());
        $this->assertNull($response->getResult());
        $this->assertSame('idValue', $response->getId());
        $this->assertTrue($response->hasError());
        $this->assertSame(1, $response->getError()->getCode());
        $this->assertSame('errorMessage', $response->getError()->getMessage());
        $this->assertSame(['errorDataKey' => 'errorDataValue'], $response->getError()->getData());
    }

    /** @test */
    public function deserialize_emptyObjectResponse_emptyResponseReturned(): void
    {
        $serializer = new JsonArraySerializer();

        $response = $serializer->deserialize('{}', []);

        $this->assertInstanceOf(ResponseObject::class, $response);
        $this->assertSame('', $response->getProtocol());
        $this->assertNull($response->getResult());
        $this->assertNull($response->getId());
        $this->assertFalse($response->hasError());
    }

    /** @test */
    public function deserialize_emptyStringResponse_nullReturned(): void
    {
        $serializer = new JsonArraySerializer();

        $response = $serializer->deserialize(' ', []);

        $this->assertNull($response);
    }

    /** @test */
    public function deserialize_notAnObjectResponse_exceptionThrown(): void
    {
        $serializer = new JsonArraySerializer();

        $this->expectException(InvalidResponseException::class);

        $serializer->deserialize('invalid', []);
    }

    /** @test */
    public function deserialize_singleSuccessfulResponseInArray_responseWithResultReturnedInArray(): void
    {
        $serializer = new JsonArraySerializer();
        $serializedResponse = '
        [
            {
                "jsonrpc": "2.0",
                "result": "resultValue",
                "id": "idValue"
            }
        ]
        ';

        $responses = $serializer->deserialize($serializedResponse, []);

        $this->assertIsArray($responses);
        $response = $responses[0];
        $this->assertInstanceOf(ResponseObject::class, $response);
        $this->assertSame('2.0', $response->getProtocol());
        $this->assertSame('resultValue', $response->getResult());
        $this->assertSame('idValue', $response->getId());
        $this->assertFalse($response->hasError());
    }

    /** @test */
    public function deserialize_singleResponseWithObjectResult_responseWithAssociativeArrayResultReturned(): void
    {
        $serializer = new JsonArraySerializer();
        $serializedResponse = '
        {
            "jsonrpc": "2.0",
            "result": {
                "key": "value"
            },
            "id": "idValue"
        }
        ';

        $response = $serializer->deserialize($serializedResponse, []);

        $this->assertInstanceOf(ResponseObject::class, $response);
        $this->assertSame('2.0', $response->getProtocol());
        $this->assertSame(['key' => 'value'], $response->getResult());
        $this->assertSame('idValue', $response->getId());
        $this->assertFalse($response->hasError());
    }
}
