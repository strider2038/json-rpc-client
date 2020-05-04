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
class ConnectionOptions
{
    public const DEFAULT_ATTEMPT_TIMEOUT = 100000; // 100 ms
    public const DEFAULT_TIMEOUT_MULTIPLIER = 2.0;
    public const DEFAULT_MAX_ATTEMPTS = 5;

    /**
     * Reconnection attempt timeout in microseconds. Must be greater than zero.
     *
     * @var int
     */
    private $attemptTimeoutUs;

    /**
     * Used to increase timeout value with growing reconnection attempts. Must be greater than 1.0.
     *
     * Use 1 for linear scale:
     *   1 attempt: timeout = 0 ms
     *   2 attempt: timeout = 100 ms
     *   3 attempt: timeout = 100 ms
     *   4 attempt: timeout = 100 ms
     *   5 attempt: timeout = 100 ms
     *
     * Use 2 for quadratic scale:
     *   1 attempt: timeout = 0 ms
     *   2 attempt: timeout = 100 ms
     *   3 attempt: timeout = 200 ms
     *   4 attempt: timeout = 400 ms
     *   5 attempt: timeout = 800 ms
     *
     * @var float
     */
    private $timeoutMultiplier;

    /**
     * Max sequential attempts to reconnect with a remote server before fatal exception will be thrown.
     * Must be greater than or equal to 1.
     *
     * @var int
     */
    private $maxAttempts;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(
        int $attemptTimeoutUs = self::DEFAULT_ATTEMPT_TIMEOUT,
        float $timeoutMultiplier = self::DEFAULT_TIMEOUT_MULTIPLIER,
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS
    ) {
        if ($attemptTimeoutUs <= 0) {
            throw new InvalidConfigException('Timeout must be greater than 0.');
        }
        if ($timeoutMultiplier < 1.0) {
            throw new InvalidConfigException('Timeout multiplier must be greater than or equal to 1.0');
        }
        if ($maxAttempts <= 0) {
            throw new InvalidConfigException('Max attempts must be greater or equal to 1');
        }

        $this->attemptTimeoutUs = $attemptTimeoutUs;
        $this->timeoutMultiplier = $timeoutMultiplier;
        $this->maxAttempts = $maxAttempts;
    }

    public function getAttemptTimeoutUs(): int
    {
        return $this->attemptTimeoutUs;
    }

    public function getTimeoutMultiplier(): float
    {
        return $this->timeoutMultiplier;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * @throws InvalidConfigException
     */
    public static function createFromArray(array $options): self
    {
        return new self(
            $options['attempt_timeout_us'] ?? self::DEFAULT_ATTEMPT_TIMEOUT,
            $options['timeout_multiplier'] ?? self::DEFAULT_TIMEOUT_MULTIPLIER,
            $options['max_attempts'] ?? self::DEFAULT_MAX_ATTEMPTS
        );
    }
}
