<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Resources\Normalizer;

use Strider2038\JsonRpcClient\Tests\Resources\Object\ComplexObject;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ComplexObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        assert($object instanceof ComplexObject);

        return [
            'id'   => $object->getId(),
            'name' => $object->getName(),
        ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ComplexObject;
    }

    public function denormalize($data, $type, $format = null, array $context = []): ComplexObject
    {
        assert(is_array($data));

        return new ComplexObject($data['id'], $data['name'], ['meta' => 'data']);
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return ComplexObject::class === $type;
    }
}
