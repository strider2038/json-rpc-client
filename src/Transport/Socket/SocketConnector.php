<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Transport\Socket;

use Strider2038\JsonRpcClient\Exception\ConnectionFailedException;

/**
 * @internal
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SocketConnector
{
    /**
     * @throws ConnectionFailedException
     */
    public function open(string $url, int $connectionTimeoutUs, int $requestTimeoutUs): SocketConnection
    {
        $errorCode = null;
        $errorDescription = null;

        $stream = @stream_socket_client($url, $errorCode, $errorDescription, (float) $connectionTimeoutUs / 1000000);

        if (!is_resource($stream)) {
            throw new ConnectionFailedException($url, sprintf('%d: %s', $errorCode, $errorDescription));
        }

        if (false === stream_set_timeout($stream, 0, $requestTimeoutUs)) {
            throw new ConnectionFailedException($url, 'failed to set request timeout');
        }

        return new SocketConnection($stream, $url, $requestTimeoutUs);
    }

    public function wait(int $us): void
    {
        usleep($us);
    }
}
