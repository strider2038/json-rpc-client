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

use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Psr18Transport implements TransportInterface
{
    /** @var ClientInterface */
    private $client;

    /** @var string */
    private $uri;

    /** @var array */
    private $headers;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(ClientInterface $client, string $uri, array $headers = [])
    {
        if (!class_exists(Request::class)) {
            throw new InvalidConfigException(
                'Cannot create PSR-18 transport: package "nyholm/psr7" is required.'
            );
        }

        $this->client = $client;
        $this->uri = $uri;
        $this->headers = $headers;

        $this->headers['Content-Type'] = 'application/json';
    }

    public function send(string $request): string
    {
        $response = $this->sendRequest($request);

        return $this->getResponseContents($response);
    }

    /**
     * @throws RemoteProcedureCallFailedException
     */
    private function sendRequest(string $request): ResponseInterface
    {
        $psrRequest = new Request('POST', $this->uri, $this->headers, $request);

        try {
            $response = $this->client->sendRequest($psrRequest);
        } catch (ClientExceptionInterface $exception) {
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
