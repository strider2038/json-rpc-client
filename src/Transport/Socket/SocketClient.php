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
use Strider2038\JsonRpcClient\Exception\ConnectionLostException;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SocketClient
{
    /**
     * Connection in URL format.
     *
     * @var string
     */
    private $url;

    /**
     * Connection timeout in seconds.
     *
     * @var float
     */
    private $connectionTimeoutS;

    /**
     * Request timeout in microseconds.
     *
     * @var int
     */
    private $requestTimeoutUs;

    /** @var resource|null */
    private $connection;

    public function __construct(string $url, int $connectionTimeoutUs, int $requestTimeoutUs)
    {
        $this->url = $url;
        $this->connectionTimeoutS = ((float) $connectionTimeoutUs) / 1000000;
        $this->requestTimeoutUs = $requestTimeoutUs;
    }

    public function __destruct()
    {
        if (is_resource($this->connection)) {
            fclose($this->connection);
        }
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
        $this->sendRequest($connection, $request);

        return $this->receiveResponse($connection);
    }

    /**
     * Recreates connection. Can be used to reconnect.
     *
     * @throws ConnectionFailedException
     */
    public function connect(): void
    {
        $this->connection = $this->createConnection();
    }

    /**
     * @throws ConnectionFailedException
     * @throws ConnectionLostException
     *
     * @return resource
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->createConnection();
        }

        if (feof($this->connection)) {
            throw new ConnectionLostException($this->url, 'eof received');
        }

        return $this->connection;
    }

    /**
     * @throws ConnectionFailedException
     *
     * @return resource
     */
    private function createConnection()
    {
        $start = microtime(true);

        while (true) {
            $connection = @stream_socket_client($this->url, $errno, $errstr, $this->connectionTimeoutS);
            if (is_resource($connection)) {
                break;
            }

            if (microtime(true) - $start >= $this->connectionTimeoutS) {
                break;
            }

            usleep(100);
        }

        if (!is_resource($connection)) {
            throw new ConnectionFailedException($this->url, sprintf('%d: %s', $errno, $errstr));
        }

        if (false === stream_set_timeout($connection, 0, $this->requestTimeoutUs)) {
            throw new ConnectionFailedException($this->url, 'failed to set request timeout');
        }

        return $connection;
    }

    /**
     * @param resource $connection
     *
     * @throws ConnectionLostException
     */
    private function sendRequest($connection, string $request): void
    {
        if (false === fwrite($connection, $request."\n")) {
            throw new ConnectionLostException($this->url, 'failed to write data into stream');
        }

        fflush($connection);
    }

    /**
     * @param resource $connection
     *
     * @throws RemoteProcedureCallFailedException
     */
    private function receiveResponse($connection): string
    {
        $response = fgets($connection);

        if (false === $response) {
            $this->checkForTimeout($connection);

            throw new RemoteProcedureCallFailedException(sprintf('Failed to get response from %s.', $this->url));
        }

        return $response;
    }

    /**
     * @param resource $stream
     *
     * @throws RemoteProcedureCallFailedException
     */
    private function checkForTimeout($stream): void
    {
        $info = stream_get_meta_data($stream);

        if ($info['timed_out']) {
            $error = sprintf('Request to %s failed by timeout %d us.', $this->url, $this->requestTimeoutUs);

            throw new RemoteProcedureCallFailedException($error);
        }
    }
}
