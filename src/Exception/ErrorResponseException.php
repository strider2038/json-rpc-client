<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Exception;

use Strider2038\JsonRpcClient\Response\ErrorObject;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ErrorResponseException extends \Exception implements JsonRpcClientExceptionInterface
{
    private ErrorObject $error;

    public function __construct(ErrorObject $error)
    {
        $this->error = $error;

        parent::__construct(
            sprintf(
                'Server response has error: code %d, message "%s", data %s.',
                $error->getCode(),
                $error->getMessage(),
                json_encode($error->getData())
            )
        );
    }

    public function getError(): ErrorObject
    {
        return $this->error;
    }
}
