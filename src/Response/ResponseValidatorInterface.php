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

use Strider2038\JsonRpcClient\Exception\ResponseException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface ResponseValidatorInterface
{
    /**
     * @param $response
     * @throws ResponseException
     */
    public function validate($response): void;
}
