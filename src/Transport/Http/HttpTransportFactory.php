<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Transport\Http;

use GuzzleHttp\Client;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Transport\TransportFactoryInterface;
use Strider2038\JsonRpcClient\Transport\TransportInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @internal
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class HttpTransportFactory implements TransportFactoryInterface
{
    public function createTransport(string $connection, GeneralOptions $options): TransportInterface
    {
        $client = $this->detectHttpClientType($options);

        if (HttpTransportTypeInterface::SYMFONY === $client) {
            $transport = $this->createSymfonyTransport($connection, $options);
        } else {
            $transport = $this->createGuzzleTransport($connection, $options);
        }

        return $transport;
    }

    /**
     * @throws InvalidConfigException
     */
    private function detectHttpClientType(GeneralOptions $options): string
    {
        $client = $options->getHttpClient();

        if (HttpTransportTypeInterface::AUTODETECT === $client) {
            if (class_exists(HttpClient::class)) {
                $client = HttpTransportTypeInterface::SYMFONY;
            } elseif (class_exists(Client::class)) {
                $client = HttpTransportTypeInterface::GUZZLE;
            } else {
                throw new InvalidConfigException(
                    'Cannot create HTTP client: one of packages "symfony/http-client" or "guzzlehttp/guzzle" is required.'
                );
            }
        }

        return $client;
    }

    /**
     * @throws InvalidConfigException
     */
    private function createSymfonyTransport(string $connection, GeneralOptions $options): SymfonyTransport
    {
        if (!class_exists(HttpClient::class)) {
            throw new InvalidConfigException(
                'Cannot create Symfony HTTP client: package "symfony/http-client" is required.'
            );
        }

        $config = array_merge(
            $options->getTransportConfiguration(),
            [
                'timeout' => (float) $options->getRequestTimeoutUs() / 1000000,
            ]
        );

        $client = HttpClient::createForBaseUri($connection, $config);

        return new SymfonyTransport($client);
    }

    /**
     * @throws InvalidConfigException
     */
    private function createGuzzleTransport(string $connection, GeneralOptions $options): GuzzleTransport
    {
        if (!class_exists(Client::class)) {
            throw new InvalidConfigException(
                'Cannot create Guzzle HTTP client: package "guzzlehttp/guzzle" is required.'
            );
        }

        $config = array_merge(
            $options->getTransportConfiguration(),
            [
                'base_uri' => $connection,
                'timeout'  => (float) $options->getRequestTimeoutUs() / 1000000,
            ]
        );

        $client = new Client($config);

        return new GuzzleTransport($client);
    }
}
