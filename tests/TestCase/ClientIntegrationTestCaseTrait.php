<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\TestCase;

use PHPUnit\Framework\Assert;
use Strider2038\JsonRpcClient\ClientInterface;
use Strider2038\JsonRpcClient\Exception\ErrorResponseException;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait ClientIntegrationTestCaseTrait
{
    /** @test */
    public function singleRequest_positionalParameters_resultReturned(): void
    {
        $client = $this->createClient();

        $result = $client->call('sum', [1, 2, 4]);

        Assert::assertSame(7, $result);
    }

    /** @test */
    public function singleRequest_namedParameters_resultReturned(): void
    {
        $client = $this->createClient();
        $params = [
            'subtrahend' => 23,
            'minuend'    => 42,
        ];

        $result = $client->call('subtract', $params);

        Assert::assertSame(19, $result);
    }

    /** @test */
    public function nonExistentMethod_positionalParameters_exceptionThrown(): void
    {
        $client = $this->createClient();

        try {
            $client->call('non-existent-method', [1, 2, 3]);
        } catch (\Throwable $exception) {
            Assert::assertInstanceOf(ErrorResponseException::class, $exception);
            Assert::assertStringContainsString('Server response has error: code -32601', $exception->getMessage());
        }
    }

    /** @test */
    public function batchRequest_validParameters_orderedResultsReturned(): void
    {
        $client = $this->createClient();
        $subtractionParams = [
            'subtrahend' => 23,
            'minuend'    => 42,
        ];
        $reflectParams = ['key' => 'value'];

        $results = $client->batch()
            ->call('sum', [1, 2, 4])
            ->notify('notify', $subtractionParams)
            ->call('subtract', $subtractionParams)
            ->call('reflect', $reflectParams)
            ->send();

        Assert::assertIsArray($results);
        Assert::assertCount(4, $results);
        Assert::assertSame(7, $results[0]);
        Assert::assertNull($results[1]);
        Assert::assertSame(19, $results[2]);
        Assert::assertSame(['key' => 'value'], (array) $results[3]);
    }

    /** @test */
    public function singleRequest_timeout_exceptionThrown(): void
    {
        $client = $this->createClient();

        try {
            $client->call('sleep', [1500]);
        } catch (\Throwable $exception) {
            Assert::assertInstanceOf(RemoteProcedureCallFailedException::class, $exception);
        }
    }

    abstract protected function createClient(): ClientInterface;
}
