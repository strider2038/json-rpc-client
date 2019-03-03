<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Response;

use Strider2038\JsonRpcClient\Exception\ErrorResponseException;
use Strider2038\JsonRpcClient\Exception\ResponseException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ExceptionalResponseValidator implements ResponseValidatorInterface
{
    public function validate($response): void
    {
        if ($response !== null) {
            $this->validateResponse($response);
        }
    }

    private function validateResponse($response): void
    {
        if (is_array($response)) {
            foreach ($response as $responseInBatch) {
                $this->validateSingleResponse($responseInBatch);
            }
        } else {
            $this->validateSingleResponse($response);
        }
    }

    private function validateSingleResponse($response): void
    {
        if (!$response instanceof ResponseObjectInterface) {
            throw new ResponseException(
                sprintf(
                    'Response from server expected to be an object or an array of objects. Given "%s".',
                    json_encode($response)
                )
            );
        }

        if ($response->getJsonRpcVersion() !== '2.0') {
            throw new ResponseException('Invalid JSON RPC version in server request.');
        }

        if ($response->hasError()) {
            throw new ErrorResponseException($response->getError());
        }
    }
}
