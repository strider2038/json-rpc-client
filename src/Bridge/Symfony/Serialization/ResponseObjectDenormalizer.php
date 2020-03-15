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

use Strider2038\JsonRpcClient\Request\RequestObject;
use Strider2038\JsonRpcClient\Response\ErrorObject;
use Strider2038\JsonRpcClient\Response\ResponseObject;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseObjectDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use DenormalizerAwareTrait;

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return ResponseObject::class === $type;
    }

    public function denormalize($data, $type, $format = null, array $context = []): ResponseObject
    {
        if (!is_array($data)) {
            throw new UnexpectedValueException('Denormalization data is expected to be an array');
        }

        $response = new ResponseObject(
            $data['jsonrpc'] ?? '',
            $this->denormalizeResult($data, $format, $context),
            $data['id'] ?? null
        );

        if (array_key_exists('error', $data)) {
            $response->setError($this->denormalizeError($data, $format, $context));
        }

        return $response;
    }

    private function denormalizeResult($data, $format, array $context)
    {
        $result = $data['result'] ?? null;

        $request = $context['json_rpc']['request'] ?? null;

        if ($request instanceof RequestObject) {
            $method = $request->getMethod();

            $resultTypesByMethods = $context['json_rpc']['result_types_by_methods'] ?? [];

            if (null !== $result && array_key_exists($method, $resultTypesByMethods)) {
                $resultType = $resultTypesByMethods[$method];

                $result = $this->denormalizer->denormalize($result, $resultType, $format, $context);
            }
        }

        return $result;
    }

    private function denormalizeError($data, $format, array $context): ErrorObject
    {
        return new ErrorObject(
            $data['error']['code'] ?? 0,
            $data['error']['message'] ?? 'unknown error',
            $this->denormalizeErrorData($data['error']['data'] ?? null, $format, $context)
        );
    }

    private function denormalizeErrorData($errorData, $format, array $context)
    {
        $errorType = $this->detectErrorType($context);

        if (null !== $errorData && null !== $errorType) {
            $errorData = $this->denormalizer->denormalize($errorData, $errorType, $format, $context);
        }

        return $errorData;
    }

    private function detectErrorType(array $context): ?string
    {
        $errorType = $context['json_rpc']['default_error_type'] ?? null;

        $request = $context['json_rpc']['request'] ?? null;
        if ($request instanceof RequestObject) {
            $errorType = $context['json_rpc']['error_types_by_methods'][$request->getMethod()] ?? $errorType;
        }

        return $errorType;
    }
}
