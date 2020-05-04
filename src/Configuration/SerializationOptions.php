<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Configuration;

use Strider2038\JsonRpcClient\Exception\InvalidConfigException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SerializationOptions
{
    public const OBJECT_SERIALIZER = 'object';
    public const ARRAY_SERIALIZER = 'array';
    public const SYMFONY_SERIALIZER = 'symfony';
    public const DEFAULT_SERIALIZER = self::OBJECT_SERIALIZER;

    private const SUPPORTED_SERIALIZERS = [
        self::OBJECT_SERIALIZER,
        self::ARRAY_SERIALIZER,
        self::SYMFONY_SERIALIZER,
    ];

    /** @var string */
    private $serializerType;

    /**
     * Used to deserialize successful server response to defined class or type.
     *
     * Example
     *
     * [
     *     'createProduct' => CreateProductResponse::class,
     * ]
     *
     * Works only with Symfony serializer.
     *
     * @var string[]
     */
    private $resultTypesByMethods;

    /**
     * Used to deserialize error data from server response to defined class or type. It can be used
     * when all error data has the same structure or as fallback type for errors. If server can respond
     * with specific error data on method you can use errorTypesByMethods option.
     *
     * @var string|null
     */
    private $defaultErrorType;

    /**
     * Used to deserialize error data from server response after call to specific method.
     *
     * Example
     *
     * [
     *     'createProduct' => CreateProductErrors::class,
     * ]
     *
     * @var string[]
     */
    private $errorTypesByMethods;

    public function __construct(
        string $serializerType = self::DEFAULT_SERIALIZER,
        array $resultTypesByMethods = [],
        string $errorType = null,
        array $errorTypesByMethods = []
    ) {
        $this->validateSerializerType($serializerType);

        $this->serializerType = $serializerType;
        $this->resultTypesByMethods = $resultTypesByMethods;
        $this->defaultErrorType = $errorType;
        $this->errorTypesByMethods = $errorTypesByMethods;
    }

    public function getSerializerType(): string
    {
        return $this->serializerType;
    }

    public function getResultTypesByMethods(): array
    {
        return $this->resultTypesByMethods;
    }

    public function getDefaultErrorType(): ?string
    {
        return $this->defaultErrorType;
    }

    public function getErrorTypesByMethods(): array
    {
        return $this->errorTypesByMethods;
    }

    public static function createFromArray(array $options): self
    {
        return new self(
            $options['serializer_type'] ?? self::DEFAULT_SERIALIZER,
            $options['result_types_by_methods'] ?? [],
            $options['default_error_type'] ?? null,
            $options['error_types_by_methods'] ?? []
        );
    }

    private function validateSerializerType(string $serializer): void
    {
        if (!in_array($serializer, self::SUPPORTED_SERIALIZERS, true)) {
            throw new InvalidConfigException(
                sprintf(
                    'Serializer type option must be equal to one of: %s.',
                    implode(
                        ', ',
                        array_map(
                            static function (string $s): string {
                                return '"'.$s.'"';
                            },
                            self::SUPPORTED_SERIALIZERS
                        )
                    )
                )
            );
        }
    }
}
