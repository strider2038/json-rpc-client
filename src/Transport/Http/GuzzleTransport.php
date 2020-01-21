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

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

/**
 * @internal
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class GuzzleTransport implements TransportInterface
{
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws RemoteProcedureCallFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $request): string
    {
        $response = $this->sendRequest($request);

        return $this->getResponseContents($response);
    }

    /**
     * @throws RemoteProcedureCallFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendRequest(string $request): ResponseInterface
    {
        try {
            $response = $this->client->request('POST', '', [
                'body'    => $request,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
        } catch (RequestException $exception) {
            throw new RemoteProcedureCallFailedException(
                sprintf('JSON RPC request failed with error: %s.', $exception->getMessage()),
                0,
                $exception
            );
        }

        return $response;
    }

    /**
     * @throws RemoteProcedureCallFailedException
     */
    private function getResponseContents(ResponseInterface $response): string
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody();
        $contents = $body->getContents();

        if (200 !== $statusCode) {
            throw new RemoteProcedureCallFailedException(
                sprintf('JSON RPC request failed with error %d: %s.', $statusCode, $contents)
            );
        }

        return $contents;
    }
}
