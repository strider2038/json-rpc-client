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
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ConnectionOptionsTest extends TestCase
{
    /** @test */
    public function construct_noParameters_defaultValues(): void
    {
        $options = new ConnectionOptions();

        $this->assertSame(ConnectionOptions::DEFAULT_ATTEMPT_TIMEOUT, $options->getAttemptTimeoutUs());
        $this->assertSame(ConnectionOptions::DEFAULT_TIMEOUT_MULTIPLIER, $options->getTimeoutMultiplier());
        $this->assertSame(ConnectionOptions::DEFAULT_MAX_ATTEMPTS, $options->getMaxAttempts());
    }

    /**
     * @test
     * @dataProvider invalidParametersProvider
     */
    public function construct_invalidParameters_invalidConfigException(
        int $timeoutUs,
        float $timeoutMultiplier,
        int $maxAttempts,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage($expectedMessage);

        new ConnectionOptions($timeoutUs, $timeoutMultiplier, $maxAttempts);
    }

    public function invalidParametersProvider(): \Iterator
    {
        yield 'invalid timeout' => [0, 0, 0, 'Timeout must be greater than 0'];
        yield 'invalid timeout multiplier' => [1, 0.999, 0, 'Timeout multiplier must be greater than or equal to 1.0'];
        yield 'invalid max attempts' => [1, 1, 0, 'Max attempts must be greater or equal to 1'];
    }

    /** @test */
    public function createFromArray_emptyArray_defaultValues(): void
    {
        $options = ConnectionOptions::createFromArray([]);

        $this->assertSame(ConnectionOptions::DEFAULT_ATTEMPT_TIMEOUT, $options->getAttemptTimeoutUs());
        $this->assertSame(ConnectionOptions::DEFAULT_TIMEOUT_MULTIPLIER, $options->getTimeoutMultiplier());
        $this->assertSame(ConnectionOptions::DEFAULT_MAX_ATTEMPTS, $options->getMaxAttempts());
    }

    /** @test */
    public function createFromArray_optionsInArray_optionsWithValuesCreated(): void
    {
        $options = ConnectionOptions::createFromArray([
            'attempt_timeout_us' => 1,
            'timeout_multiplier' => 1.5,
            'max_attempts'       => 3,
        ]);

        $this->assertSame(1, $options->getAttemptTimeoutUs());
        $this->assertSame(1.5, $options->getTimeoutMultiplier());
        $this->assertSame(3, $options->getMaxAttempts());
    }
}
