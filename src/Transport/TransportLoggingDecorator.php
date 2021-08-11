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

use Psr\Log\LoggerInterface;

/**
 * @internal
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class TransportLoggingDecorator implements TransportInterface
{
    private TransportInterface $decorated;

    private LoggerInterface $logger;

    public function __construct(TransportInterface $decorated, LoggerInterface $logger)
    {
        $this->decorated = $decorated;
        $this->logger = $logger;
    }

    public function send(string $request): string
    {
        $this->logger->debug('Sending JSON RPC request.', ['body' => $request]);

        $sentAt = microtime(true);

        $response = $this->decorated->send($request);

        $this->logger->debug(
            'JSON RPC response received.',
            [
                'body'     => $response,
                'duration' => microtime(true) - $sentAt,
            ]
        );

        return $response;
    }
}
