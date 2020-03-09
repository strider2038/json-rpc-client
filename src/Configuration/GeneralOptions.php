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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class GeneralOptions
{
    public const DEFAULT_REQUEST_TIMEOUT = 1000000;

    /**
     * Request timeout in microseconds.
     *
     * @var int
     */
    private $requestTimeoutUs;

    /**
     * Connection configuration
     *
     * @var ConnectionOptions
     */
    private $connectionOptions;

    /**
     * Serialization configuration
     *
     * @var SerializationOptions
     */
    private $serializationOptions;

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
        SerializationOptions $serializationOptions = null,
        array $transportConfiguration = []
    ) {
        if ($requestTimeoutUs <= 0) {
            throw new InvalidConfigException('Request timeout must be greater than 0.');
        }

        $this->requestTimeoutUs = $requestTimeoutUs;
        $this->connectionOptions = $connectionOptions ?? new ConnectionOptions();
        $this->transportConfiguration = $transportConfiguration;
        $this->serializationOptions = $serializationOptions ?? new SerializationOptions();
    }

    public function getRequestTimeoutUs(): int
    {
        return $this->requestTimeoutUs;
    }

    public function getConnectionOptions(): ConnectionOptions
    {
        return $this->connectionOptions;
    }

    public function getTransportConfiguration(): array
    {
        return $this->transportConfiguration;
    }

    public function getSerializationOptions(): SerializationOptions
    {
        return $this->serializationOptions;
    }

    /**
     * @throws InvalidConfigException
     */
    public static function createFromArray(array $options): self
    {
        return new self(
            $options['request_timeout_us'] ?? self::DEFAULT_REQUEST_TIMEOUT,
            ConnectionOptions::createFromArray($options['connection'] ?? []),
            SerializationOptions::createFromArray($options['serialization'] ?? []),
            $options['transport_configuration'] ?? []
        );
    }
}
