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

    public function getUrl(): string
    {
        return $this->url;
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
     * Sends request under socket client and returns response.
     *
     * @throws ConnectionFailedException
     * @throws ConnectionLostException
     * @throws RemoteProcedureCallFailedException
     */
    public function send(string $request): string
    {
        $connection = $this->getConnection();

        $this->sendData($connection, $request);
        $response = fgets($connection);
        $this->checkForTimeout($connection);

        return $response;
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
        $client = stream_socket_client($this->url, $errno, $errstr, $this->connectionTimeoutS);

        if (!is_resource($client)) {
            throw new ConnectionFailedException($this->url, sprintf('%d: %s', $errno, $errstr));
        }

        if (false === stream_set_timeout($client, 0, $this->requestTimeoutUs)) {
            throw new ConnectionFailedException($this->url, 'cannot set timeout');
        }

        return $client;
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

    /**
     * @param resource $connection
     *
     * @throws ConnectionLostException
     */
    private function sendData($connection, string $request): void
    {
        if (false === fwrite($connection, $request."\n")) {
            throw new ConnectionLostException($this->url, 'failed to write data into stream');
        }

        fflush($connection);
    }
}
