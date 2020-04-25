<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Configuration;

use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Transport\Http\HttpTransportTypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class GeneralOptions
{
    public const DEFAULT_REQUEST_TIMEOUT = 1000000;

    private const SUPPORTED_HTTP_CLIENTS = [
        HttpTransportTypeInterface::AUTODETECT,
        HttpTransportTypeInterface::GUZZLE,
        HttpTransportTypeInterface::SYMFONY,
    ];

    /**
     * Request timeout in microseconds.
     *
     * @var int
     */
    private $requestTimeoutUs;

    /**
     * Connection configuration.
     *
     * @var ConnectionOptions
     */
    private $connectionOptions;

    /**
     * If enabled then ProcessingClient will be used with response unpacking.
     * If disabled then RawClient will be used, that will return ResponseObjectInterface
     * for each request.
     *
     * @var bool
     */
    private $enableResponseProcessing = true;

    /**
     * Serialization configuration.
     *
     * @var SerializationOptions
     */
    private $serializationOptions;

    /**
     * Preferred HTTP client.
     *
     * @see HttpTransportTypeInterface for available options.
     *
     * @var string
     */
    private $httpClientType;

    /**
     * @var array
     */
    private $transportConfiguration;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(
        int $requestTimeoutUs = self::DEFAULT_REQUEST_TIMEOUT,
        ConnectionOptions $connectionOptions = null,
        bool $enableResponseProcessing = true,
        SerializationOptions $serializationOptions = null,
        array $transportConfiguration = [],
        string $httpClient = HttpTransportTypeInterface::AUTODETECT
    ) {
        if ($requestTimeoutUs <= 0) {
            throw new InvalidConfigException('Request timeout must be greater than 0.');
        }
        $this->validateHttpClient($httpClient);

        $this->requestTimeoutUs = $requestTimeoutUs;
        $this->connectionOptions = $connectionOptions ?? new ConnectionOptions();
        $this->enableResponseProcessing = $enableResponseProcessing;
        $this->transportConfiguration = $transportConfiguration;
        $this->serializationOptions = $serializationOptions ?? new SerializationOptions();
        $this->httpClientType = $httpClient;
    }

    public function getRequestTimeoutUs(): int
    {
        return $this->requestTimeoutUs;
    }

    public function getConnectionOptions(): ConnectionOptions
    {
        return $this->connectionOptions;
    }

    public function isResponseProcessingEnabled(): bool
    {
        return $this->enableResponseProcessing;
    }

    public function getTransportConfiguration(): array
    {
        return $this->transportConfiguration;
    }

    public function getSerializationOptions(): SerializationOptions
    {
        return $this->serializationOptions;
    }

    public function getHttpClientType(): string
    {
        return $this->httpClientType;
    }

    /**
     * @throws InvalidConfigException
     */
    public static function createFromArray(array $options): self
    {
        return new self(
            $options['request_timeout_us'] ?? self::DEFAULT_REQUEST_TIMEOUT,
            ConnectionOptions::createFromArray($options['connection'] ?? []),
            $options['enable_response_processing'] ?? true,
            SerializationOptions::createFromArray($options['serialization'] ?? []),
            $options['transport_configuration'] ?? [],
            $options['http_client_type'] ?? HttpTransportTypeInterface::AUTODETECT
        );
    }

    /**
     * @throws InvalidConfigException
     */
    private function validateHttpClient(string $httpClient): void
    {
        if (!in_array($httpClient, self::SUPPORTED_HTTP_CLIENTS, true)) {
            throw new InvalidConfigException(
                sprintf(
                    'Invalid value "%s" for http client option. Must be one of: %s.',
                    $httpClient,
                    implode(
                        ', ',
                        array_map(
                            static function (string $s): string {
                                return '"'.$s.'"';
                            },
                            self::SUPPORTED_HTTP_CLIENTS
                        )
                    )
                )
            );
        }
    }
}
