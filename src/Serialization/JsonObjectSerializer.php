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
class JsonObjectSerializer implements MessageSerializerInterface
{
    private int $encodeOptions;

    private int $decodeOptions;

    private int $depth;

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
            $decodedResponse = json_decode($response, false, $this->depth, $this->decodeOptions);
            $result = $this->deserializeResponse($decodedResponse, $response);
        }

        return $result;
    }

    private function deserializeResponse($decodedResponse, string $response)
    {
        $result = [];

        if (is_array($decodedResponse)) {
            foreach ($decodedResponse as $decodedObject) {
                $result[] = $this->deserializeResponseObject($decodedObject, $response);
            }
        } else {
            $result = $this->deserializeResponseObject($decodedResponse, $response);
        }

        return $result;
    }

    private function deserializeResponseObject($decodedObject, string $response): ResponseObject
    {
        if (!is_object($decodedObject)) {
            throw new InvalidResponseException($response);
        }

        $responseObject = new ResponseObject(
            $decodedObject->jsonrpc ?? '',
            $decodedObject->result ?? null,
            $decodedObject->id ?? null
        );

        if (!empty($decodedObject->error)) {
            $decodedError = $decodedObject->error;

            $errorObject = new ErrorObject(
                $decodedError->code ?? 0,
                $decodedError->message ?? 'unknown error',
                $decodedError->data ?? null
            );

            $responseObject->setError($errorObject);
        }

        return $responseObject;
    }
}
