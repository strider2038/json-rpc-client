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

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Transport\Socket\SocketClient;
use Strider2038\JsonRpcClient\Transport\Socket\SocketConnector;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class TransportFactory
{
    /** @var LoggerInterface|null */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @throws InvalidConfigException
     */
    public function createTransport(string $connection, GeneralOptions $options): TransportInterface
    {
        $this->validateUrl($connection);
        $protocol = strtolower(parse_url($connection, PHP_URL_SCHEME));

        if ('tcp' === $protocol) {
            $transport = $this->createTcpTransport($connection, $options);
        } elseif ('http' === $protocol || 'https' === $protocol) {
            $transport = $this->createHttpTransport($connection, $options);
        } else {
            throw new InvalidConfigException(
                sprintf(
                    'Unsupported protocol: "%s". Supported protocols: "tcp", "http", "https".',
                    $protocol
                )
            );
        }

        if ($this->logger) {
            $transport = new TransportLoggingDecorator($transport, $this->logger);
        }

        return $transport;
    }

    /**
     * @throws InvalidConfigException
     */
    private function validateUrl(string $url): void
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidConfigException(
                sprintf('Valid URL is expected for TCP/IP transport. Given value is "%s".', $url)
            );
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function createTcpTransport(string $connection, GeneralOptions $options): SocketTransport
    {
        $connector = new SocketConnector();
        $client = new SocketClient($connector, $connection, $options->getConnectionOptions(), $options->getRequestTimeoutUs());

        return new SocketTransport($client);
    }

    private function createHttpTransport(string $connection, GeneralOptions $options): GuzzleHttpTransport
    {
        $guzzle = new Client([
            'base_uri' => $connection,
            'timeout'  => (float) $options->getRequestTimeoutUs() / 1000000,
        ]);

        return new GuzzleHttpTransport($guzzle);
    }
}
