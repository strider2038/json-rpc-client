<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;
use Strider2038\JsonRpcClient\Request\RandomIntegerIdGenerator;
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;
use Strider2038\JsonRpcClient\Request\UuidGenerator;
use Strider2038\JsonRpcClient\Response\ExceptionalResponseValidator;
use Strider2038\JsonRpcClient\Serialization\JsonObjectSerializer;
use Strider2038\JsonRpcClient\Service\Caller;
use Strider2038\JsonRpcClient\Service\HighLevelClient;
use Strider2038\JsonRpcClient\Transport\GuzzleHttpTransport;
use Strider2038\JsonRpcClient\Transport\TcpTransport;
use Strider2038\JsonRpcClient\Transport\TransportInterface;
use Strider2038\JsonRpcClient\Transport\TransportLoggingDecorator;

/**
 * @experimental API may be changed
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientFactory
{
    /** @var LoggerInterface|null */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function createClient(string $connection, array $options = []): ClientInterface
    {
        $requestObjectFactory = $this->createRequestObjectFactory();
        $transport = $this->createTransport($connection, $options);
        $caller = $this->createCaller($transport);

        return new HighLevelClient($requestObjectFactory, $caller);
    }

    private function createRequestObjectFactory(): RequestObjectFactory
    {
        if (class_exists(Uuid::class)) {
            $idGenerator = new UuidGenerator();
        } else {
            $idGenerator = new RandomIntegerIdGenerator();
        }

        return new RequestObjectFactory($idGenerator);
    }

    private function createCaller(TransportInterface $transport): Caller
    {
        $serializer = new JsonObjectSerializer();
        $validator = new ExceptionalResponseValidator();

        return new Caller($serializer, $transport, $validator);
    }

    private function createTransport(string $connection, array $options): TransportInterface
    {
        $scheme = strtolower(parse_url($connection, PHP_URL_SCHEME));
        $timeout = (float) ($options['timeout_ms'] ?? 1000);

        if ('tcp' === $scheme) {
            $transport = new TcpTransport($connection, (int) $timeout);
        } elseif ('http' === $scheme || 'https' === $scheme) {
            $guzzle = new Client([
                'base_uri' => $connection,
                'timeout'  => $timeout / 1000,
            ]);

            $transport = new GuzzleHttpTransport($guzzle);
        } else {
            throw new InvalidConfigException(
                sprintf(
                    'Unsupported protocol: "%s". Supported protocols: "tcp", "http", "https".',
                    $scheme
                )
            );
        }

        if ($this->logger) {
            $transport = new TransportLoggingDecorator($transport, $this->logger);
        }

        return $transport;
    }
}
