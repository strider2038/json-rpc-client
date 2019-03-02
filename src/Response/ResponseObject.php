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
class ResponseObject implements ResponseObjectInterface
{
    /** @var string */
    public $jsonrpc;

    /** @var mixed */
    public $result;

    /** @var ErrorObject|null */
    public $error;

    /** @var string|int|null */
    public $id;

    public function getId()
    {
        return $this->id;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }
}
