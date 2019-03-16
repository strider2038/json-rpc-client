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
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class TcpTransport implements TransportInterface
{
    /** @var string */
    private $url;

    /** @var int */
    private $timeoutMs;

    /** @var resource|null */
    private $client;

    /**
     * @param string $url
     * @param int $timeoutMs
     * @throws InvalidConfigException
     */
    public function __construct(string $url, int $timeoutMs = 1000)
    {
        $this->validateUrl($url);

        $this->url = $url;
        $this->timeoutMs = $timeoutMs;
    }

    public function send(string $request): string
    {
        $client = $this->getSocketClient();

        fwrite($client, $request);
        fwrite($client, "\n");
        fflush($client);

        $response = fgets($client);

        $this->validateMetaData($client);

        return $response;
    }

    /**
     * @param string $url
     * @throws InvalidConfigException
     */
    private function validateUrl(string $url): void
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidConfigException(
                sprintf('Valid URL is expected for TCP/IP transport. Given value is "%s".', $url)
            );
        }

        if (parse_url($url, PHP_URL_SCHEME) !== 'tcp') {
            throw new InvalidConfigException(
                sprintf('URL for TCP/IP transport must start with "tcp://" scheme. Given value is "%s".', $url)
            );
        }
    }

    /**
     * @return resource
     * @throws ConnectionFailedException
     */
    private function getSocketClient()
    {
        if ($this->client === null) {
            $this->client = $this->createSocketClient();
        }

        return $this->client;
    }

    /**
     * @return resource
     * @throws ConnectionFailedException
     */
    private function createSocketClient()
    {
        $client = stream_socket_client($this->url, $errno, $errstr, ((float)$this->timeoutMs) / 1000);

        if (!is_resource($client)) {
            throw new ConnectionFailedException($this->url, sprintf('%d: %s', $errno, $errstr));
        }

        if (stream_set_timeout($client, 0, $this->timeoutMs * 1000) === false) {
            throw new ConnectionFailedException($this->url, 'cannot set timeout');
        }

        return $client;
    }

    private function validateMetaData($client): void
    {
        $info = stream_get_meta_data($client);

        if ($info['timed_out']) {
            $errorMessage = sprintf('JSON RPC request to %s failed by timeout %d ms.', $this->url, $this->timeoutMs);

            throw new RemoteProcedureCallFailedException($errorMessage);
        }
    }

    public function __destruct()
    {
        if (is_resource($this->client)) {
            fclose($this->client);
        }
    }
}
