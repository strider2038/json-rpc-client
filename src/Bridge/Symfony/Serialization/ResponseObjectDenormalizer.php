<?php
/*
 * This file is part of json-rpc-client.
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

        $response = new ResponseObject();
        $response->jsonrpc = $data['jsonrpc'] ?? '';
        $response->id = $data['id'] ?? null;
        $response->result = $this->denormalizeResult($data, $format, $context);

        if (array_key_exists('error', $data)) {
            $response->error = $this->denormalizeError($data, $format, $context);
        }

        return $response;
    }

    private function denormalizeResult($data, $format, array $context)
    {
        /** @var RequestObject $request */
        $request = $context['json_rpc']['request'];
        $resultTypesByMethods = $context['json_rpc']['result_types_by_methods'] ?? [];

        $result = $data['result'] ?? null;

        if (null !== $result && array_key_exists($request->method, $resultTypesByMethods)) {
            $resultType = $resultTypesByMethods[$request->method];

            $result = $this->denormalizer->denormalize($result, $resultType, $format, $context);
        }

        return $result;
    }

    private function denormalizeError($data, $format, array $context): ErrorObject
    {
        $error = new ErrorObject();
        $error->code = $data['error']['code'] ?? null;
        $error->message = $data['error']['message'] ?? null;
        $error->data = $this->denormalizeErrorData($data['error']['data'] ?? null, $format, $context);

        return $error;
    }

    private function denormalizeErrorData($errorData, $format, array $context)
    {
        $errorType = $context['json_rpc']['error_type'] ?? null;

        if (null !== $errorData && null !== $errorType) {
            $errorData = $this->denormalizer->denormalize($errorData, $errorType, $format, $context);
        }

        return $errorData;
    }
}
