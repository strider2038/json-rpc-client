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
use Psr\Log\LoggerInterface;
use Strider2038\JsonRpcClient\Transport\TransportInterface;
use Strider2038\JsonRpcClient\Transport\TransportLoggingDecorator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class TransportLoggingDecoratorTest extends TestCase
{
    private const REQUEST_DATA = 'requestData';
    private const RESPONSE_DATA = 'responseData';

    /** @var TransportInterface */
    private $decorated;

    /** @var LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        $this->decorated = \Phake::mock(TransportInterface::class);
        $this->logger = \Phake::mock(LoggerInterface::class);
    }

    /** @test */
    public function send_request_requestSentAndResponseReturnedAndOperationsLogged(): void
    {
        $transport = new TransportLoggingDecorator($this->decorated, $this->logger);
        $expectedResponse = $this->givenReturnedResponse();

        $response = $transport->send(self::REQUEST_DATA);

        $this->assertRequestWasSent();
        $this->assertSame($expectedResponse, $response);
        $this->assertRequestWasLogged();
        $this->assertResponseWasLogged($expectedResponse);
    }

    private function assertRequestWasSent(): void
    {
        \Phake::verify($this->decorated)
            ->send(self::REQUEST_DATA);
    }

    private function givenReturnedResponse(): string
    {
        $response = self::RESPONSE_DATA;

        \Phake::when($this->decorated)
            ->send(\Phake::anyParameters())
            ->thenReturn($response);

        return $response;
    }

    private function assertRequestWasLogged(): void
    {
        \Phake::verify($this->logger)
            ->debug('Sending JSON RPC request.', ['body' => self::REQUEST_DATA]);
    }

    private function assertResponseWasLogged(string $expectedResponse): void
    {
        \Phake::verify($this->logger)
            ->debug('JSON RPC response received.', \Phake::capture($context));
        $this->assertIsArray($context);
        $this->assertArrayHasKey('body', $context);
        $this->assertSame($expectedResponse, $context['body']);
        $this->assertArrayHasKey('duration', $context);
        $this->assertGreaterThan(0, $context['duration']);
    }
}
