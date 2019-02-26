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
class NotificationObject implements RequestObjectInterface
{
    /** @var string */
    public $jsonrpc = '2.0';

    /** @var string */
    public $method;

    /** @var mixed */
    public $params;

    public function __construct(string $method, $params)
    {
        $this->method = $method;
        $this->params = $params;
    }
}
