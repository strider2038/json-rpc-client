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
class NotificationObject implements RequestObjectInterface, \JsonSerializable
{
    /** @var string */
    private $jsonrpc = '2.0';

    /** @var string */
    private $method;

    /** @var mixed */
    private $params;

    public function __construct(string $method, $params)
    {
        $this->method = $method;
        $this->params = $params;
    }

    public function getId()
    {
        return null;
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
        ];
    }
}
