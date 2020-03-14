<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Transport\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;
use Strider2038\JsonRpcClient\Transport\Http\Psr18Transport;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Psr18TransportTest extends TestCase
{
    private const REQUEST_DATA = 'requestData';
    private const RESPONSE_DATA = 'responseData';
    private const URI = 'uri';

    /** @var ClientInterface */
    private $client;

    protected function setUp(): void
    {
        $this->client = \Phake::mock(ClientInterface::class);
    }

    /** @test */
    public function send_requestWithSuccessfulResponse_requestSentAndResponseDataReturned(): void
    {
        $transport = $this->createTransport();
        $stream = $this->givenStreamWithContents(self::RESPONSE_DATA);
        $response = $this->givenSuccessfulResponse($stream);
        $this->givenResponseReturnedFromServer($response);

        $responseData = $transport->send(self::REQUEST_DATA);

        $this->assertRequestWasSent();
        $this->assertResponseStatusCodeWasExtracted($response);
        $this->assertResponseBodyWasExtracted($response);
        $this->assertStreamContentsWasExtracted($stream);
        $this->assertSame(self::RESPONSE_DATA, $responseData);
    }

    /** @test */
    public function send_requestWithFailedResponse_exceptionThrown(): void
    {
        $transport = $this->createTransport();
        $stream = $this->givenStreamWithContents(self::RESPONSE_DATA);
        $response = $this->givenFailedResponse($stream);
        $this->givenResponseReturnedFromServer($response);

        $this->expectException(RemoteProcedureCallFailedException::class);
        $this->expectExceptionMessage('JSON RPC request failed with error 403: responseData.');

        $transport->send(self::REQUEST_DATA);
    }

    /** @test */
    public function send_exceptionDuringRequest_exceptionThrown(): void
    {
        $transport = $this->createTransport();
        $this->givenClientThrowsException(new class('error') extends \Exception implements ClientExceptionInterface {
        });

        $this->expectException(RemoteProcedureCallFailedException::class);
        $this->expectExceptionMessage('JSON RPC request failed with error: error.');

        $transport->send(self::REQUEST_DATA);
    }

    private function assertRequestWasSent(): void
    {
        /* @var RequestInterface $request */
        \Phake::verify($this->client)
            ->sendRequest(\Phake::capture($request));
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::URI, $request->getUri()->getPath());
        $this->assertSame(self::REQUEST_DATA, (string) $request->getBody());
        $this->assertSame('application/json', $request->getHeader('Content-Type')[0]);
    }

    private function assertResponseBodyWasExtracted(ResponseInterface $response): void
    {
        \Phake::verify($response)
            ->getBody();
    }

    private function assertResponseStatusCodeWasExtracted(ResponseInterface $response): void
    {
        \Phake::verify($response)
            ->getStatusCode();
    }

    private function assertStreamContentsWasExtracted(StreamInterface $stream): void
    {
        \Phake::verify($stream)
            ->getContents();
    }

    private function givenResponseReturnedFromServer(ResponseInterface $response): void
    {
        \Phake::when($this->client)
            ->sendRequest(\Phake::anyParameters())
            ->thenReturn($response);
    }

    private function givenClientThrowsException(\Throwable $exception): void
    {
        \Phake::when($this->client)
            ->sendRequest(\Phake::anyParameters())
            ->thenThrow($exception);
    }

    private function givenStreamWithContents(string $expectedResponseData): StreamInterface
    {
        $stream = \Phake::mock(StreamInterface::class);

        \Phake::when($stream)
            ->getContents()
            ->thenReturn($expectedResponseData);

        return $stream;
    }

    private function givenSuccessfulResponse(StreamInterface $stream): ResponseInterface
    {
        $response = \Phake::mock(ResponseInterface::class);

        \Phake::when($response)
            ->getStatusCode()
            ->thenReturn(200);

        \Phake::when($response)
            ->getBody()
            ->thenReturn($stream);

        return $response;
    }

    private function givenFailedResponse(StreamInterface $stream): ResponseInterface
    {
        $response = \Phake::mock(ResponseInterface::class);

        \Phake::when($response)
            ->getStatusCode()
            ->thenReturn(403);

        \Phake::when($response)
            ->getBody()
            ->thenReturn($stream);

        return $response;
    }

    private function createTransport(): Psr18Transport
    {
        return new Psr18Transport($this->client, self::URI);
    }
}
