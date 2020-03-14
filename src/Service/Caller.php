<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Service;

use Strider2038\JsonRpcClient\Request\RequestObjectInterface;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Strider2038\JsonRpcClient\Response\ResponseValidatorInterface;
use Strider2038\JsonRpcClient\Serialization\ContextGenerator;
use Strider2038\JsonRpcClient\Serialization\MessageSerializerInterface;
use Strider2038\JsonRpcClient\Transport\TransportInterface;

/**
 * @internal
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Caller
{
    /** @var MessageSerializerInterface */
    private $serializer;

    /** @var ContextGenerator */
    private $contextGenerator;

    /** @var TransportInterface */
    private $transport;

    /** @var ResponseValidatorInterface */
    private $validator;

    public function __construct(
        MessageSerializerInterface $serializer,
        ContextGenerator $contextGenerator,
        TransportInterface $transport,
        ResponseValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->contextGenerator = $contextGenerator;
        $this->transport = $transport;
        $this->validator = $validator;
    }

    /**
     * @param RequestObjectInterface|RequestObjectInterface[] $request
     *
     * @return ResponseObjectInterface|ResponseObjectInterface[]|null
     */
    public function call($request)
    {
        $serializedRequest = $this->serializer->serialize($request);
        $serializedResponse = $this->transport->send($serializedRequest);
        $context = $this->contextGenerator->createSerializationContext($request);
        $response = $this->serializer->deserialize($serializedResponse, $context);
        $this->validator->validate($response);

        return $response;
    }
}
