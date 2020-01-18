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
    public const DEFAULT_CONNECTION_TIMEOUT = 1000000;
    public const DEFAULT_REQUEST_TIMEOUT = 1000000;

    /**
     * Connection timeout in microseconds.
     *
     * @var int
     */
    private $connectionTimeoutUs;

    /**
     * Request timeout in microseconds.
     *
     * @var int
     */
    private $requestTimeoutUs;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(
        int $connectionTimeoutUs = self::DEFAULT_CONNECTION_TIMEOUT,
        int $requestTimeoutUs = self::DEFAULT_REQUEST_TIMEOUT
    ) {
        if ($connectionTimeoutUs <= 0 || $requestTimeoutUs <= 0) {
            throw new InvalidConfigException('Timeout must be greater than 0.');
        }

        $this->connectionTimeoutUs = $connectionTimeoutUs;
        $this->requestTimeoutUs = $requestTimeoutUs;
    }

    public function getConnectionTimeoutUs(): int
    {
        return $this->connectionTimeoutUs;
    }

    public function getRequestTimeoutUs(): int
    {
        return $this->requestTimeoutUs;
    }

    /**
     * @throws InvalidConfigException
     */
    public static function createFromArray(array $options): self
    {
        return new self(
            $options['connection_timeout_us'] ?? self::DEFAULT_CONNECTION_TIMEOUT,
            $options['request_timeout_us'] ?? self::DEFAULT_REQUEST_TIMEOUT
        );
    }
}
