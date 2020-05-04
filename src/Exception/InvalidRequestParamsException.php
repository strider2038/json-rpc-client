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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class InvalidRequestParamsException extends \Exception implements JsonRpcClientExceptionInterface
{
    public function __construct($params)
    {
        parent::__construct(
            sprintf(
                'Request params must be object or array, value of type %s given.',
                gettype($params)
            )
        );
    }
}
