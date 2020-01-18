<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Transport;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Exception\ConnectionFailedException;
use Strider2038\JsonRpcClient\Exception\ConnectionLostException;
use Strider2038\JsonRpcClient\Transport\Socket\SocketClient;
use Strider2038\JsonRpcClient\Transport\SocketTransport;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SocketTransportTest extends TestCase
{
    private const REQUEST = 'request';
    private const RESPONSE = 'response';

    /** @var SocketClient */
    private $client;

    protected function setUp(): void
    {
        $this->client = \Phake::mock(SocketClient::class);
    }

    /** @test */
    public function send_successfulRequest_response(): void
    {
        $this->givenResponseReturnedFromClient();
        $transport = $this->createTransport();

        $response = $transport->send(self::REQUEST);

        $this->assertSame(self::RESPONSE, $response);
        $this->assertRequestSentByClient();
    }

    /** @test */
    public function send_unexpectedClientException_exceptionThrown(): void
    {
        $this->givenClientThrowsExceptionOnSend(new \Exception());
        $transport = $this->createTransport();

        $this->expectException(\Exception::class);

        $transport->send(self::REQUEST);
    }

    /** @test */
    public function send_connectionLostAndReconnectSucceeded_response(): void
    {
        \Phake::when($this->client)
            ->send(self::REQUEST)
            ->thenThrow(new ConnectionLostException('', ''))
            ->thenReturn(self::RESPONSE);
        $transport = $this->createTransport();

        $response = $transport->send(self::REQUEST);

        $this->assertSame(self::RESPONSE, $response);
        $this->assertRequestSentByClient(2);
        $this->assertClientReconnected();
    }

    /** @test */
    public function send_connectionLostAndCannotReconnect_connectionFailedException(): void
    {
        $this->givenClientThrowsExceptionOnSend(new ConnectionLostException('', ''));
        \Phake::when($this->client)
            ->connect()
            ->thenThrow(new ConnectionFailedException('', ''));
        $transport = $this->createTransport();
        $expectedException = null;

        try {
            $transport->send(self::REQUEST);
        } catch (\Throwable $expectedException) {
        }

        $this->assertNotNull($expectedException);
        $this->assertInstanceOf(ConnectionFailedException::class, $expectedException);
        $this->assertRequestSentByClient(1);
        $this->assertClientReconnected();
    }

    private function givenResponseReturnedFromClient(): void
    {
        \Phake::when($this->client)
            ->send(self::REQUEST)
            ->thenReturn(self::RESPONSE);
    }

    private function givenClientThrowsExceptionOnSend(\Throwable $exception): void
    {
        \Phake::when($this->client)
            ->send(self::REQUEST)
            ->thenThrow($exception);
    }

    private function assertRequestSentByClient(int $times = 1): void
    {
        \Phake::verify($this->client, \Phake::times($times))
            ->send(\Phake::anyParameters());
    }

    private function assertClientReconnected(): void
    {
        \Phake::verify($this->client)
            ->connect(\Phake::anyParameters());
    }

    private function createTransport(): SocketTransport
    {
        return new SocketTransport($this->client);
    }
}
