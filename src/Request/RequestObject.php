<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Request;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RequestObject implements RequestObjectInterface, \JsonSerializable
{
    private string $jsonrpc = '2.0';

    private string $method;

    /** @var mixed */
    private $params;

    /** @var string|int */
    private $id;

    public function __construct($id, string $method, $params)
    {
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getProtocol(): string
    {
        return $this->jsonrpc;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function jsonSerialize(): array
    {
        return [
            'jsonrpc' => $this->jsonrpc,
            'method'  => $this->method,
            'params'  => $this->params,
            'id'      => $this->id,
        ];
    }
}
