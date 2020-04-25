<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Configuration\SerializationOptions;
use Strider2038\JsonRpcClient\Exception\InvalidConfigException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SerializationOptionsTest extends TestCase
{
    /** @test */
    public function construct_noParameters_defaultValues(): void
    {
        $options = new SerializationOptions();

        $this->assertSame(SerializationOptions::DEFAULT_SERIALIZER, $options->getSerializerType());
        $this->assertSame([], $options->getResultTypesByMethods());
        $this->assertNull($options->getDefaultErrorType());
        $this->assertSame([], $options->getErrorTypesByMethods());
    }

    /** @test */
    public function construct_invalidSerializer_exceptionThrown(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Serializer type option must be equal to one of: "object", "array", "symfony".');

        new SerializationOptions('');
    }

    /** @test */
    public function createFromArray_emptyArray_defaultValues(): void
    {
        $options = SerializationOptions::createFromArray([]);

        $this->assertSame(SerializationOptions::DEFAULT_SERIALIZER, $options->getSerializerType());
        $this->assertSame([], $options->getResultTypesByMethods());
        $this->assertNull($options->getDefaultErrorType());
        $this->assertSame([], $options->getErrorTypesByMethods());
    }

    /** @test */
    public function createFromArray_optionsInArray_optionsWithValuesCreated(): void
    {
        $options = SerializationOptions::createFromArray([
            'serializer_type'         => 'array',
            'result_types_by_methods' => ['method' => 'type'],
            'default_error_type'      => 'errorType',
            'error_types_by_methods'  => ['method' => 'error_type'],
        ]);

        $this->assertSame(SerializationOptions::ARRAY_SERIALIZER, $options->getSerializerType());
        $this->assertSame(['method' => 'type'], $options->getResultTypesByMethods());
        $this->assertSame('errorType', $options->getDefaultErrorType());
        $this->assertSame(['method' => 'error_type'], $options->getErrorTypesByMethods());
    }
}
