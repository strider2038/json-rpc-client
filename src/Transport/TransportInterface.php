<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Transport;

use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface TransportInterface
{
    /**
     * @param string $request
     *
     * @return string
     *
     * @throws RemoteProcedureCallFailedException
     */
    public function send(string $request): string;
}
