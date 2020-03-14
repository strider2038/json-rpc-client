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
interface RequestObjectInterface
{
    /**
     * JSON RPC protocol version
     * Always returns '2.0'.
     */
    public function getProtocol(): string;

    /**
     * Returns request identifier unique for session. Returns null for notification.
     *
     * @return string|int|null
     */
    public function getId();

    /**
     * Returns remote procedure method name.
     */
    public function getMethod(): string;

    /**
     * Returns request parameters.
     */
    public function getParams();
}
