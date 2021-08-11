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

use Strider2038\JsonRpcClient\Exception\ConnectionFailedException;
use Strider2038\JsonRpcClient\Exception\ConnectionLostException;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;
use Strider2038\JsonRpcClient\Transport\Socket\SocketClient;

/**
 * @internal
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SocketTransport implements TransportInterface
{
    private SocketClient $client;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(SocketClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws ConnectionFailedException
     * @throws ConnectionLostException
     * @throws RemoteProcedureCallFailedException
     */
    public function send(string $request): string
    {
        try {
            $response = $this->client->send($request);
        } catch (ConnectionLostException $exception) {
            $this->client->connect();

            $response = $this->client->send($request);
        }

        return $response;
    }
}
