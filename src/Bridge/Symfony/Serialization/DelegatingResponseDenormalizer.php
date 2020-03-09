<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Bridge\Symfony\Serialization;

use Strider2038\JsonRpcClient\Response\ResponseObject;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class DelegatingResponseDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use DenormalizerAwareTrait;

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return ResponseObjectInterface::class === $type;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (!is_array($data)) {
            throw new UnexpectedValueException('Denormalization data is expected to be an array');
        }

        $response = null;

        if (array_key_exists('jsonrpc', $data)) {
            $response = $this->denormalizer->denormalize($data, ResponseObject::class, $format, $context);
        } else {
            $response = $this->denormalizeBatchResponses($data, $format, $context);
        }

        return $response;
    }

    private function denormalizeBatchResponses($data, $format, array $context): array
    {
        $responses = [];

        foreach ($data as $singleResponse) {
            $singleContext = $this->createResponseContext($singleResponse, $context);
            $responses[] = $this->denormalizer->denormalize($singleResponse, ResponseObject::class, $format, $singleContext);
        }

        return $responses;
    }

    private function createResponseContext(array $response, array $context): array
    {
        if (!array_key_exists('id', $response)) {
            throw new LogicException('Response has no id');
        }

        $id = $response['id'];

        if (!array_key_exists($id, $context['json_rpc']['requests'])) {
            throw new LogicException(sprintf('Response id "%s" is not matching any request id', $id));
        }

        $singleContext = $context;
        $singleContext['json_rpc'] = [
            'request' => $context['json_rpc']['requests'][$id],
        ];

        return $singleContext;
    }
}
