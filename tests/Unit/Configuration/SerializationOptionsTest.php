<?php
/*
 * This file is part of json-rpc-client.
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

        $this->assertSame(SerializationOptions::DEFAULT_SERIALIZER, $options->getSerializer());
        $this->assertSame([], $options->getResultTypesByMethods());
        $this->assertNull($options->getErrorType());
    }

    /** @test */
    public function construct_invalidSerializer_exceptionThrown(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Serializer option must be equal to one of: "object", "array", "symfony".');

        new SerializationOptions('');
    }

    /** @test */
    public function createFromArray_emptyArray_defaultValues(): void
    {
        $options = SerializationOptions::createFromArray([]);

        $this->assertSame(SerializationOptions::DEFAULT_SERIALIZER, $options->getSerializer());
        $this->assertSame([], $options->getResultTypesByMethods());
        $this->assertNull($options->getErrorType());
    }

    /** @test */
    public function createFromArray_optionsInArray_optionsWithValuesCreated(): void
    {
        $options = SerializationOptions::createFromArray([
            'serializer'              => 'array',
            'result_types_by_methods' => ['method' => 'type'],
            'error_type'              => 'errorType',
        ]);

        $this->assertSame(SerializationOptions::ARRAY_SERIALIZER, $options->getSerializer());
        $this->assertSame(['method' => 'type'], $options->getResultTypesByMethods());
        $this->assertSame('errorType', $options->getErrorType());
    }
}
