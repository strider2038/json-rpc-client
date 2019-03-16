<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Transport;

use GuzzleHttp\ClientInterface;
use Strider2038\JsonRpcClient\Exception\RemoteProcedureCallFailedException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class GuzzleHttpTransport implements TransportInterface
{
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function send(string $request): string
    {
        $response = $this->client->request('POST', '', ['body' => $request]);

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
