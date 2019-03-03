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
    public function getJsonRpcVersion(): string;

    /**
     * @return string|int|null
     */
    public function getId();

    public function getResult();

    public function hasError(): bool;

    public function getError(): ErrorObject;
}
