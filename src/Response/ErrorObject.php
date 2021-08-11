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
class ErrorObject
{
    private int $code;

    private string $message;

    /** @var mixed */
    private $data;

    public function __construct(int $code, string $message, $data)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData()
    {
        return $this->data;
    }
}
