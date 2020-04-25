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

use Psr\Log\LoggerInterface;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Transport\Http\HttpTransportFactory;
use Strider2038\JsonRpcClient\Transport\Socket\SocketClient;
use Strider2038\JsonRpcClient\Transport\Socket\SocketConnector;

/**
 * @internal
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MultiTransportFactory implements TransportFactoryInterface
{
    /** @var HttpTransportFactory */
    private $httpTransportFactory;

    /** @var LoggerInterface|null */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->httpTransportFactory = new HttpTransportFactory();
        $this->logger = $logger;
    }

    /**
     * @throws InvalidConfigException
     */
    public function createTransport(string $url, GeneralOptions $options): TransportInterface
    {
        $protocol = $this->parseProtocol($url);

        if ('tcp' === $protocol || 'unix' === $protocol) {
            $transport = $this->createSocketTransport($url, $options);
        } elseif ('http' === $protocol || 'https' === $protocol) {
            $transport = $this->httpTransportFactory->createTransport($url, $options);
        } else {
            throw new InvalidConfigException(
                sprintf(
                    'Unsupported protocol: "%s". Supported protocols: "unix", "tcp", "http", "https".',
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
    private function createSocketTransport(string $connection, GeneralOptions $options): SocketTransport
    {
        $connector = new SocketConnector();
        $client = new SocketClient($connector, $connection, $options->getConnectionOptions(), $options->getRequestTimeoutUs());

        return new SocketTransport($client);
    }

    private function parseProtocol(string $connection): string
    {
        $protocol = '';

        if (false !== preg_match('/^(.*):/U', $connection, $matches)) {
            $protocol = strtolower($matches[1] ?? '');
        }

        return $protocol;
    }
}
