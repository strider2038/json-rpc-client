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
use Strider2038\JsonRpcClient\Response\ErrorObject;
use Strider2038\JsonRpcClient\Response\ResponseObject;
use Strider2038\JsonRpcClient\Serialization\JsonObjectSerializer;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class JsonObjectSerializerTest extends TestCase
{
    /** @test */
    public function serialize_singleRequest_jsonStringReturned(): void
    {
        $serializer = new JsonObjectSerializer();
        $request = new RequestObject('method', ['parameterName' => 'parameterValue']);
        $request->id = 'id';

        $serializedRequest = $serializer->serialize($request);

        $this->assertSame(
            '{"jsonrpc":"2.0","method":"method","params":{"parameterName":"parameterValue"},"id":"id"}',
            $serializedRequest
        );
    }

    /** @test */
    public function serialize_singleNotification_jsonStringReturned(): void
    {
        $serializer = new JsonObjectSerializer();
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
        $serializer = new JsonObjectSerializer();
        $request = new RequestObject('method', ['parameterName' => 'parameterValue']);
        $request->id = 'id';

        $serializedRequest = $serializer->serialize([$request]);

        $this->assertSame(
            '[{"jsonrpc":"2.0","method":"method","params":{"parameterName":"parameterValue"},"id":"id"}]',
            $serializedRequest
        );
    }

    /** @test */
    public function deserialize_singleSuccessfulResponse_responseWithResultReturned(): void
    {
        $serializer = new JsonObjectSerializer();
        $serializedResponse = '
        {
            "jsonrpc": "2.0",
            "result": "resultValue",
            "id": "idValue"
        }
        ';

        $response = $serializer->deserialize($serializedResponse);

        $this->assertInstanceOf(ResponseObject::class, $response);
        $this->assertSame('2.0', $response->jsonrpc);
        $this->assertSame('resultValue', $response->result);
        $this->assertSame('idValue', $response->id);
        $this->assertNull($response->error);
    }

    /** @test */
    public function deserialize_singleErrorResponse_responseWithErrorReturned(): void
    {
        $serializer = new JsonObjectSerializer();
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

        $response = $serializer->deserialize($serializedResponse);

        $this->assertInstanceOf(ResponseObject::class, $response);
        $this->assertSame('2.0', $response->jsonrpc);
        $this->assertNull($response->result);
        $this->assertSame('idValue', $response->id);
        $this->assertInstanceOf(ErrorObject::class, $response->error);
        $this->assertSame(1, $response->error->code);
        $this->assertSame('errorMessage', $response->error->message);
        $this->assertIsObject($response->error->data);
    }

    /** @test */
    public function deserialize_emptyObjectResponse_emptyResponseReturned(): void
    {
        $serializer = new JsonObjectSerializer();

        $response = $serializer->deserialize('{}');

        $this->assertInstanceOf(ResponseObject::class, $response);
        $this->assertNull($response->jsonrpc);
        $this->assertNull($response->result);
        $this->assertNull($response->id);
        $this->assertNull($response->error);
    }

    /** @test */
    public function deserialize_notAnObjectResponse_exceptionThrown(): void
    {
        $serializer = new JsonObjectSerializer();

        $this->expectException(InvalidResponseException::class);

        $serializer->deserialize('invalid');
    }

    /** @test */
    public function deserialize_singleSuccessfulResponseInArray_responseWithResultReturnedInArray(): void
    {
        $serializer = new JsonObjectSerializer();
        $serializedResponse = '
        [
            {
                "jsonrpc": "2.0",
                "result": "resultValue",
                "id": "idValue"
            }
        ]
        ';

        $responses = $serializer->deserialize($serializedResponse);

        $this->assertIsArray($responses);
        $response = $responses[0];
        $this->assertInstanceOf(ResponseObject::class, $response);
        $this->assertSame('2.0', $response->jsonrpc);
        $this->assertSame('resultValue', $response->result);
        $this->assertSame('idValue', $response->id);
        $this->assertNull($response->error);
    }
}
