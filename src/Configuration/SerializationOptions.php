<?php
/*
 * This file is part of json-rpc-client.
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
    private $serializer;

    /** @var string[] */
    private $typesByMethods;

    /** @var string|null */
    private $errorType;

    public function __construct(
        string $serializer = self::DEFAULT_SERIALIZER,
        array $typesByMethods = [],
        string $errorType = null
    ) {
        $this->validateSerializer($serializer);

        $this->serializer = $serializer;
        $this->typesByMethods = $typesByMethods;
        $this->errorType = $errorType;
    }

    public function getSerializer(): string
    {
        return $this->serializer;
    }

    public function getTypesByMethods(): array
    {
        return $this->typesByMethods;
    }

    public function getErrorType(): ?string
    {
        return $this->errorType;
    }

    public static function createFromArray(array $options): self
    {
        return new self(
            $options['serializer'] ?? self::DEFAULT_SERIALIZER,
            $options['types_by_methods'] ?? [],
            $options['error_type'] ?? null
        );
    }

    private function validateSerializer(string $serializer): void
    {
        if (!in_array($serializer, self::SUPPORTED_SERIALIZERS, true)) {
            throw new InvalidConfigException(
                sprintf(
                    'Serializer option must be equal to one of: %s.',
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
