<?php
/*
 * This file is part of json-rpc-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Transport\Socket;

use Strider2038\JsonRpcClient\Exception\ConnectionLostException;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SocketConnection
{
    /** @var resource */
    private $stream;

    /** @var string */
    private $url;

    /** @var int */
    private $requestTimeoutUs;

    public function __construct($stream, string $url, int $requestTimeoutUs)
    {
        $this->stream = $stream;
        $this->url = $url;
        $this->requestTimeoutUs = $requestTimeoutUs;
    }

    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * @throws ConnectionLostException
     */
    public function sendRequest(string $request): void
    {
        if (false === fwrite($this->stream, $request."\n")) {
            throw new ConnectionLostException($this->url, 'failed to write data into stream');
        }

        fflush($this->stream);
    }

    /**
     * @throws RemoteProcedureCallFailedException
     */
    public function receiveResponse(): string
    {
        $response = fgets($this->stream);

        if (false === $response) {
            $this->checkForTimeout($this->stream);

            throw new RemoteProcedureCallFailedException(sprintf('Failed to get response from %s.', $this->url));
        }

        return $response;
    }

    public function isClosed(): bool
    {
        return feof($this->stream);
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
