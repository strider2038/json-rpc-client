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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface ResponseObjectInterface
{
    /**
     * JSON RPC protocol version
     * Always returns '2.0'.
     */
    public function getProtocol(): string;

    /**
     * Returns request identifier unique for session.
     *
     * @return string|int|null
     */
    public function getId();

    /**
     * Returns remote procedure call result.
     */
    public function getResult();

    /**
     * Always use this method to check for errors in response.
     */
    public function hasError(): bool;

    /**
     * Returns error description if response has error. Otherwise will be type exception.
     * Always use hasError() method before retrieving error.
     */
    public function getError(): ErrorObject;
}
