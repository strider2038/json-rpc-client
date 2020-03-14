<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Transport\Http;

use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;
use Strider2038\JsonRpcClient\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SymfonyTransport implements TransportInterface
{
    /** @var HttpClientInterface */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws RemoteProcedureCallFailedException
     */
    public function send(string $request): string
    {
        try {
            $response = $this->client->request('POST', '', [
                'body'    => $request,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $this->validateResponse($response);
            $content = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw new RemoteProcedureCallFailedException(
                sprintf('JSON RPC request failed with error: %s.', $exception->getMessage()),
                0,
                $exception
            );
        }

        return $content;
    }

    /**
     * @throws RemoteProcedureCallFailedException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        if (200 !== $statusCode) {
            throw new RemoteProcedureCallFailedException(
                sprintf('JSON RPC request failed with error %d: %s.', $statusCode, $response->getContent(false))
            );
        }
    }
}
