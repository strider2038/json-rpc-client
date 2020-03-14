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
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;
use Strider2038\JsonRpcClient\Transport\Http\SymfonyTransport;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SymfonyTransportTest extends TestCase
{
    private const REQUEST_DATA = 'requestData';
    private const RESPONSE_DATA = 'responseData';

    /** @var HttpClientInterface */
    private $client;

    protected function setUp(): void
    {
        $this->client = \Phake::mock(HttpClientInterface::class);
    }

    /** @test */
    public function send_requestWithSuccessfulResponse_requestSentAndResponseDataReturned(): void
    {
        $transport = new SymfonyTransport($this->client);
        $response = $this->givenSuccessfulResponse(self::RESPONSE_DATA);
        $this->givenResponseReturnedFromServer($response);

        $responseData = $transport->send(self::REQUEST_DATA);

        $this->assertSame(self::RESPONSE_DATA, $responseData);
        $this->assertRequestWasSent();
        $this->assertResponseStatusCodeWasExtracted($response);
    }

    /** @test */
    public function send_requestWithFailedResponse_exceptionThrown(): void
    {
        $transport = new SymfonyTransport($this->client);
        $response = $this->givenFailedResponse(self::RESPONSE_DATA);
        $this->givenResponseReturnedFromServer($response);

        $this->expectException(RemoteProcedureCallFailedException::class);
        $this->expectExceptionMessage('JSON RPC request failed with error 403: responseData.');

        $transport->send(self::REQUEST_DATA);
    }

    /** @test */
    public function send_exceptionDuringRequest_exceptionThrown(): void
    {
        $transport = new SymfonyTransport($this->client);
        $this->givenClientThrowsException(new class('error') extends \Exception implements ExceptionInterface {
        });

        $this->expectException(RemoteProcedureCallFailedException::class);
        $this->expectExceptionMessage('JSON RPC request failed with error: error.');

        $transport->send(self::REQUEST_DATA);
    }

    private function assertRequestWasSent(): void
    {
        \Phake::verify($this->client)
            ->request('POST', '', [
                'body'    => self::REQUEST_DATA,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
    }

    private function assertResponseStatusCodeWasExtracted(ResponseInterface $response): void
    {
        \Phake::verify($response)
            ->getStatusCode();
    }

    private function givenSuccessfulResponse(string $responseData): ResponseInterface
    {
        $response = \Phake::mock(ResponseInterface::class);

        \Phake::when($response)
            ->getStatusCode()
            ->thenReturn(200);

        \Phake::when($response)
            ->getContent()
            ->thenReturn($responseData);

        return $response;
    }

    private function givenFailedResponse(string $responseData): ResponseInterface
    {
        $response = \Phake::mock(ResponseInterface::class);

        \Phake::when($response)
            ->getStatusCode()
            ->thenReturn(403);

        \Phake::when($response)
            ->getContent(\Phake::anyParameters())
            ->thenReturn($responseData);

        return $response;
    }

    private function givenResponseReturnedFromServer(ResponseInterface $response): void
    {
        \Phake::when($this->client)
            ->request(\Phake::anyParameters())
            ->thenReturn($response);
    }

    private function givenClientThrowsException(\Throwable $exception): void
    {
        \Phake::when($this->client)
            ->request(\Phake::anyParameters())
            ->thenThrow($exception);
    }
}
