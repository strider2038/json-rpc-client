<?php
/*
 * This file is part of json-rpc-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Bridge\Symfony\DependencyInjection\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Strider2038\JsonRpcClient\Bridge\Symfony\Serialization\DelegatingResponseDenormalizer;
use Strider2038\JsonRpcClient\Bridge\Symfony\Serialization\ResponseObjectDenormalizer;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Can be used to instantiate Symfony serializer as standalone component. For better user experience it
 * is recommended to use library as Symfony bundle.
 *
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SerializerFactory
{
    public static function createSerializer(): SerializerInterface
    {
        $encoders = [new JsonEncoder()];

        $classMetadataFactory = self::createClassMetadataFactory();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyInfo = self::createPropertyInfo();

        $normalizers = [
            new DelegatingResponseDenormalizer(),
            new ResponseObjectDenormalizer(),
            new DateTimeNormalizer(),
            new DateTimeZoneNormalizer(),
            new DateIntervalNormalizer(),
            new JsonSerializableNormalizer($classMetadataFactory),
            new ObjectNormalizer($classMetadataFactory, null, $propertyAccessor, $propertyInfo),
        ];

        return new Serializer($normalizers, $encoders);
    }

    private static function createClassMetadataFactory(): ?ClassMetadataFactoryInterface
    {
        $classMetadataFactory = null;

        if (class_exists(AnnotationReader::class)) {
            $annotationReader = new AnnotationReader();
            $annotationLoader = new AnnotationLoader($annotationReader);
            $classMetadataFactory = new ClassMetadataFactory($annotationLoader);
        }

        return $classMetadataFactory;
    }

    private static function createPropertyInfo(): ?PropertyInfoExtractorInterface
    {
        $propertyInfo = null;

        if (class_exists(PropertyInfoExtractor::class)) {
            $phpDocExtractor = new PhpDocExtractor();
            $reflectionExtractor = new ReflectionExtractor();

            $listExtractors = [$reflectionExtractor];
            $typeExtractors = [$phpDocExtractor, $reflectionExtractor];
            $descriptionExtractors = [$phpDocExtractor];
            $accessExtractors = [$reflectionExtractor];
            $propertyInitializableExtractors = [$reflectionExtractor];

            $propertyInfo = new PropertyInfoExtractor(
                $listExtractors,
                $typeExtractors,
                $descriptionExtractors,
                $accessExtractors,
                $propertyInitializableExtractors
            );
        }

        return $propertyInfo;
    }
}
