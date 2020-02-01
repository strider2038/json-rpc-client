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

        return $this->denormalizer->denormalize($data, ResponseObject::class, $format, $context);
    }
}
