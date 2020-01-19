<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Configuration\ConnectionOptions;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class GeneralOptionsTest extends TestCase
{
    /** @test */
    public function construct_noParameters_defaultValues(): void
    {
        $options = new GeneralOptions();

        $this->assertSame(GeneralOptions::DEFAULT_REQUEST_TIMEOUT, $options->getRequestTimeoutUs());
    }

    /** @test */
    public function construct_invalidParameters_invalidConfigException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Request timeout must be greater than 0.');

        new GeneralOptions(0);
    }

    /** @test */
    public function createFromArray_emptyArray_defaultValues(): void
    {
        $options = GeneralOptions::createFromArray([]);

        $this->assertSame(GeneralOptions::DEFAULT_REQUEST_TIMEOUT, $options->getRequestTimeoutUs());
        $this->assertSame(ConnectionOptions::DEFAULT_ATTEMPT_TIMEOUT, $options->getConnectionOptions()->getAttemptTimeoutUs());
        $this->assertSame(ConnectionOptions::DEFAULT_TIMEOUT_MULTIPLIER, $options->getConnectionOptions()->getTimeoutMultiplier());
        $this->assertSame(ConnectionOptions::DEFAULT_MAX_ATTEMPTS, $options->getConnectionOptions()->getMaxAttempts());
    }

    /** @test */
    public function createFromArray_optionsInArray_optionsWithValuesCreated(): void
    {
        $options = GeneralOptions::createFromArray([
            'request_timeout_us' => 200,
            'connection'         => [
                'attempt_timeout_us' => 1,
                'timeout_multiplier' => 1.5,
                'max_attempts'       => 3,
            ],
        ]);

        $this->assertSame(200, $options->getRequestTimeoutUs());
        $this->assertSame(1, $options->getConnectionOptions()->getAttemptTimeoutUs());
        $this->assertSame(1.5, $options->getConnectionOptions()->getTimeoutMultiplier());
        $this->assertSame(3, $options->getConnectionOptions()->getMaxAttempts());
    }
}
