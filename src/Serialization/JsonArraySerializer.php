<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Serialization;

use Strider2038\JsonRpcClient\Exception\InvalidResponseException;
use Strider2038\JsonRpcClient\Response\ErrorObject;
use Strider2038\JsonRpcClient\Response\ResponseObject;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class JsonArraySerializer implements MessageSerializerInterface
{
    /** @var int */
    private $encodeOptions;

    /** @var int */
    private $decodeOptions;

    /** @var int */
    private $depth;

    public function __construct(int $encodeOptions = 0, int $decodeOptions = 0, int $depth = 512)
    {
        $this->encodeOptions = $encodeOptions;
        $this->decodeOptions = $decodeOptions;
        $this->depth = $depth;
    }

    public function serialize($request): string
    {
        return json_encode($request, $this->encodeOptions, $this->depth);
    }

    public function deserialize(string $response, array $context)
    {
        if ('' === trim($response)) {
            $result = null;
        } else {
            $decodedResponse = json_decode($response, true, $this->depth, $this->decodeOptions);
            $result = $this->deserializeResponse($decodedResponse, $response);
        }

        return $result;
    }

    private function deserializeResponse($decodedResponse, string $response)
    {
        if (!is_array($decodedResponse)) {
            throw new InvalidResponseException($response);
        }

        $result = [];

        if (array_key_exists('jsonrpc', $decodedResponse) || empty($decodedResponse)) {
            $result = $this->deserializeResponseObject($decodedResponse);
        } else {
            foreach ($decodedResponse as $decodedObject) {
                $result[] = $this->deserializeResponseObject($decodedObject);
            }
        }

        return $result;
    }

    private function deserializeResponseObject(array $decodedObject): ResponseObject
    {
        $responseObject = new ResponseObject();
        $responseObject->jsonrpc = $decodedObject['jsonrpc'] ?? '';
        $responseObject->result = $decodedObject['result'] ?? null;
        $responseObject->id = $decodedObject['id'] ?? null;

        if (array_key_exists('error', $decodedObject)) {
            $decodedError = $decodedObject['error'];

            $errorObject = new ErrorObject();
            $errorObject->code = $decodedError['code'] ?? null;
            $errorObject->message = $decodedError['message'] ?? null;
            $errorObject->data = $decodedError['data'] ?? null;

            $responseObject->error = $errorObject;
        }

        return $responseObject;
    }
}
