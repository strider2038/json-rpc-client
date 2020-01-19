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

use Strider2038\JsonRpcClient\Configuration\ConnectionOptions;
use Strider2038\JsonRpcClient\Exception\ConnectionFailedException;
use Strider2038\JsonRpcClient\Exception\ConnectionLostException;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SocketClient
{
    /**
     * @var SocketConnector
     */
    private $connector;

    /**
     * @var SocketConnection|null
     */
    private $connection;

    /**
     * Connection in URL format.
     *
     * @var string
     */
    private $url;

    /**
     * @var ConnectionOptions
     */
    private $options;

    /**
     * Request timeout in microseconds.
     *
     * @var int
     */
    private $requestTimeoutUs;

    public function __construct(
        SocketConnector $connector,
        string $url,
        ConnectionOptions $options,
        int $requestTimeoutUs
    ) {
        $this->connector = $connector;
        $this->url = $url;
        $this->options = $options;
        $this->requestTimeoutUs = $requestTimeoutUs;
    }

    /**
     * Sends request under socket client and returns response.
     *
     * @throws ConnectionFailedException          when connection to server cannot be established
     * @throws ConnectionLostException            if connection to server was lost and request cannot be sent
     * @throws RemoteProcedureCallFailedException if request to server was sent and response cannot be received.
     *                                            Be aware that request may be successfully processed by server,
     *                                            so resending the request may lead to inconsistent state
     *                                            of the server data.
     */
    public function send(string $request): string
    {
        $connection = $this->getConnection();
        if ($connection->isClosed()) {
            throw new ConnectionLostException($this->url, 'closed by server');
        }

        $connection->sendRequest($request);

        return $connection->receiveResponse();
    }

    /**
     * Forces new connection. Can be used to reconnect.
     *
     * @throws ConnectionFailedException
     */
    public function connect(): void
    {
        unset($this->connection);

        $this->connection = $this->createConnection();
    }

    /**
     * @throws ConnectionFailedException
     */
    private function getConnection(): SocketConnection
    {
        if (null === $this->connection) {
            $this->connection = $this->createConnection();
        }

        return $this->connection;
    }

    /**
     * @throws ConnectionFailedException
     */
    private function createConnection(): SocketConnection
    {
        $connection = null;

        $maxAttempts = $this->options->getMaxAttempts();
        $timeout = $this->options->getAttemptTimeoutUs();
        $multiplier = $this->options->getTimeoutMultiplier();

        $attempt = 0;

        while (true) {
            try {
                $connection = $this->connector->open($this->url, $timeout, $this->requestTimeoutUs);

                break;
            } catch (ConnectionFailedException $exception) {
                $attempt++;

                if ($attempt >= $maxAttempts) {
                    break;
                }

                $this->connector->wait($timeout);
                $timeout = (int) ($timeout * $multiplier);
            }
        }

        if (null === $connection) {
            throw new ConnectionFailedException($this->url, 'failed by timeout');
        }

        return $connection;
    }
}
