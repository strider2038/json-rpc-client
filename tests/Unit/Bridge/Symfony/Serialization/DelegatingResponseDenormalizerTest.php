<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Bridge\Symfony\Serialization;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Bridge\Symfony\Serialization\DelegatingResponseDenormalizer;
use Strider2038\JsonRpcClient\Response\ResponseObject;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class DelegatingResponseDenormalizerTest extends TestCase
{
    private const SINGLE_RESPONSE_DATA = [
        'jsonrpc' => '2.0',
    ];
    private const FORMAT = 'format';

    /** @var DenormalizerInterface */
    private $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = \Phake::mock(DenormalizerInterface::class);
    }

    /** @test */
    public function hasCacheableSupportsMethod_noParameters_true(): void
    {
        $denormalizer = new DelegatingResponseDenormalizer();

        $hasMethod = $denormalizer->hasCacheableSupportsMethod();

        $this->assertTrue($hasMethod);
    }

    /** @test */
    public function supportsDenormalization_responseObject_true(): void
    {
        $denormalizer = new DelegatingResponseDenormalizer();

        $supports = $denormalizer->supportsDenormalization([], ResponseObjectInterface::class);

        $this->assertTrue($supports);
    }

    /** @test */
    public function denormalize_invalidData_unexpectedValueException(): void
    {
        $denormalizer = new DelegatingResponseDenormalizer();

        $this->expectException(UnexpectedValueException::class);

        $denormalizer->denormalize('invalid', ResponseObjectInterface::class);
    }

    /** @test */
    public function denormalize_singleRequest_requestDenormalized(): void
    {
        $denormalizer = new DelegatingResponseDenormalizer();
        $denormalizer->setDenormalizer($this->denormalizer);
        $context = [
            'serializationContext',
        ];
        $expectedResponse = $this->givenDenormalizedResponse();

        $response = $denormalizer->denormalize(self::SINGLE_RESPONSE_DATA, ResponseObjectInterface::class, self::FORMAT, $context);

        $this->assertSame($expectedResponse, $response);
        $this->assertRequestDataWasDenormalizedWithContext($context);
    }

    private function assertRequestDataWasDenormalizedWithContext(array $context): void
    {
        \Phake::verify($this->denormalizer)
            ->denormalize(self::SINGLE_RESPONSE_DATA, ResponseObject::class, self::FORMAT, $context);
    }

    private function givenDenormalizedResponse(): ResponseObjectInterface
    {
        $response = \Phake::mock(ResponseObjectInterface::class);

        \Phake::when($this->denormalizer)
            ->denormalize(\Phake::anyParameters())
            ->thenReturn($response);

        return $response;
    }
}
