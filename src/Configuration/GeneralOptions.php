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
    public const DEFAULT_SERIALIZER = 'object';

    /**
     * Request timeout in microseconds.
     *
     * @var int
     */
    private $requestTimeoutUs;

    /**
     * Connection timeout in microseconds.
     *
     * @var ConnectionOptions
     */
    private $connectionOptions;

    /**
     * @var array
     */
    private $transportConfiguration;

    /**
     * @var string
     */
    private $serializer;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(
        int $requestTimeoutUs = self::DEFAULT_REQUEST_TIMEOUT,
        ConnectionOptions $connectionOptions = null,
        array $transportConfiguration = [],
        string $serializer = 'object'
    ) {
        if ($requestTimeoutUs <= 0) {
            throw new InvalidConfigException('Request timeout must be greater than 0.');
        }
        if (!in_array($serializer, [self::DEFAULT_SERIALIZER, 'array'], true)) {
            throw new InvalidConfigException('Serializer option must be equal to one of: "object" or "array".');
        }

        $this->requestTimeoutUs = $requestTimeoutUs;
        $this->connectionOptions = $connectionOptions ?? new ConnectionOptions();
        $this->transportConfiguration = $transportConfiguration;
        $this->serializer = $serializer;
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

    public function getSerializer(): string
    {
        return $this->serializer;
    }

    /**
     * @throws InvalidConfigException
     */
    public static function createFromArray(array $options): self
    {
        return new self(
            $options['request_timeout_us'] ?? self::DEFAULT_REQUEST_TIMEOUT,
            ConnectionOptions::createFromArray($options['connection'] ?? []),
            $options['transport_configuration'] ?? [],
            $options['serializer'] ?? self::DEFAULT_SERIALIZER
        );
    }
}
