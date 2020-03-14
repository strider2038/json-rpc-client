<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Transport\Socket;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Configuration\ConnectionOptions;
use Strider2038\JsonRpcClient\Exception\ConnectionFailedException;
use Strider2038\JsonRpcClient\Exception\ConnectionLostException;
use Strider2038\JsonRpcClient\Transport\Socket\SocketClient;
use Strider2038\JsonRpcClient\Transport\Socket\SocketConnection;
use Strider2038\JsonRpcClient\Transport\Socket\SocketConnector;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SocketClientTest extends TestCase
{
    private const REQUEST = 'request';
    private const RESPONSE = 'response';
    private const URL = 'url';
    private const ATTEMPT_TIMEOUT = 10;
    private const REQUEST_TIMEOUT = 100;

    /**
     * @var SocketConnector
     */
    private $connector;

    /**
     * @var SocketConnection
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connector = \Phake::mock(SocketConnector::class);
        $this->connection = \Phake::mock(SocketConnection::class);
    }

    /** @test */
    public function send_firstRequest_connectionEstablishedAndResponseReceived(): void
    {
        $client = $this->createClient();
        $this->givenConnectionIsEstablished();
        $this->givenConnectionIsAlive();
        $expectedResponse = $this->givenResponseFromServer();

        $response = $client->send(self::REQUEST);

        $this->assertSame($expectedResponse, $response);
        $this->assertConnectionWasOpenedWithDefaultParameters();
        $this->assertConnectionWasVerified();
        $this->assertRequestSentAndResponseReceived();
    }

    /** @test */
    public function send_connectionIsClosed_connectionLostException(): void
    {
        $client = $this->createClient();
        $this->givenConnectionIsEstablished();
        $this->givenConnectionIsClosed();

        $this->expectException(ConnectionLostException::class);
        $this->expectExceptionMessage('Connection "url" was lost: closed by server.');

        $client->send(self::REQUEST);
    }

    /** @test */
    public function connect_noParameters_connectionIsEstablished(): void
    {
        $client = $this->createClient();
        $this->givenConnectionIsEstablished();

        $client->connect();

        $this->assertConnectionWasOpenedWithDefaultParameters();
    }

    /**
     * @test
     * @dataProvider connectionOptionsAndExpectedTimeoutsProvider
     */
    public function connect_reconnectionAttempts_expectedTimeout(
        float $multiplier,
        int $maxAttempts,
        array $expectedTimeouts
    ): void {
        $client = $this->createClient(
            new ConnectionOptions(
                self::ATTEMPT_TIMEOUT,
                $multiplier,
                $maxAttempts
            )
        );
        $this->givenConnectionIsFailed();
        $expectedException = null;

        try {
            $client->connect();
        } catch (\Throwable $expectedException) {
        }

        $this->assertConnectionAttemptsWithExpectedTimeouts($expectedTimeouts);
        $this->assertNotNull($expectedException);
        $this->assertInstanceOf(ConnectionFailedException::class, $expectedException);
    }

    public function connectionOptionsAndExpectedTimeoutsProvider(): \Iterator
    {
        yield 'linear scale, 1 attempt' => [1.0, 1, []];
        yield 'linear scale, 2 attempts' => [1.0, 2, [self::ATTEMPT_TIMEOUT]];
        yield 'linear scale, 3 attempts' => [1.0, 3, [self::ATTEMPT_TIMEOUT, self::ATTEMPT_TIMEOUT]];
        yield 'linear scale, 4 attempts' => [1.0, 4, [self::ATTEMPT_TIMEOUT, self::ATTEMPT_TIMEOUT, self::ATTEMPT_TIMEOUT]];
        yield 'quadratic scale, 1 attempt' => [2.0, 1, []];
        yield 'quadratic scale, 2 attempts' => [2.0, 2, [self::ATTEMPT_TIMEOUT]];
        yield 'quadratic scale, 3 attempts' => [2.0, 3, [self::ATTEMPT_TIMEOUT, 2 * self::ATTEMPT_TIMEOUT]];
        yield 'quadratic scale, 4 attempts' => [2.0, 4, [self::ATTEMPT_TIMEOUT, 2 * self::ATTEMPT_TIMEOUT, 4 * self::ATTEMPT_TIMEOUT]];
    }

    private function givenConnectionIsEstablished(): void
    {
        \Phake::when($this->connector)
            ->open(\Phake::anyParameters())
            ->thenReturn($this->connection);
    }

    private function givenConnectionIsFailed(): void
    {
        \Phake::when($this->connector)
            ->open(\Phake::anyParameters())
            ->thenThrow(new ConnectionFailedException('', ''));
    }

    private function assertRequestSentAndResponseReceived(): void
    {
        \Phake::verify($this->connection)
            ->sendRequest(self::REQUEST);
        \Phake::verify($this->connection)
            ->receiveResponse();
    }

    private function givenResponseFromServer(): string
    {
        $response = self::RESPONSE;

        \Phake::when($this->connection)
            ->receiveResponse()
            ->thenReturn($response);

        return $response;
    }

    private function assertConnectionWasVerified(): void
    {
        \Phake::verify($this->connection)
            ->isClosed();
    }

    private function givenConnectionIsAlive(): void
    {
        \Phake::when($this->connection)
            ->isClosed()
            ->thenReturn(false);
    }

    private function givenConnectionIsClosed(): void
    {
        \Phake::when($this->connection)
            ->isClosed()
            ->thenReturn(true);
    }

    private function createClient(ConnectionOptions $options = null): SocketClient
    {
        return new SocketClient(
            $this->connector,
            self::URL,
            $options ?? new ConnectionOptions(),
            self::REQUEST_TIMEOUT
        );
    }

    private function assertConnectionWasOpenedWithDefaultParameters(): void
    {
        \Phake::verify($this->connector)
            ->open(self::URL, ConnectionOptions::DEFAULT_ATTEMPT_TIMEOUT, self::REQUEST_TIMEOUT);
    }

    private function assertConnectionAttemptsWithExpectedTimeouts(array $expectedTimeouts): void
    {
        \Phake::verify($this->connector, \Phake::times(count($expectedTimeouts) + 1))
            ->open(\Phake::anyParameters());
        \Phake::verify($this->connector, \Phake::times(count($expectedTimeouts)))
            ->wait(\Phake::captureAll($timeouts));
        $this->assertSame($expectedTimeouts, $timeouts);
    }
}
