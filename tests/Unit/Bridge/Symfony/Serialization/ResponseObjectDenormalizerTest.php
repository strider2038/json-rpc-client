<?php
/*
 * This file is part of json-rpc-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Bridge\Symfony\Serialization;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Bridge\Symfony\Serialization\ResponseObjectDenormalizer;
use Strider2038\JsonRpcClient\Request\RequestObject;
use Strider2038\JsonRpcClient\Response\ErrorObject;
use Strider2038\JsonRpcClient\Response\ResponseObject;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseObjectDenormalizerTest extends TestCase
{
    const FORMAT = 'format';
    /** @var DenormalizerInterface */
    private $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = \Phake::mock(DenormalizerInterface::class);
    }

    /** @test */
    public function hasCacheableSupportsMethod_noParameters_true(): void
    {
        $denormalizer = new ResponseObjectDenormalizer();

        $hasMethod = $denormalizer->hasCacheableSupportsMethod();

        $this->assertTrue($hasMethod);
    }

    /** @test */
    public function supportsDenormalization_responseObject_true(): void
    {
        $denormalizer = new ResponseObjectDenormalizer();

        $supports = $denormalizer->supportsDenormalization([], ResponseObject::class);

        $this->assertTrue($supports);
    }

    /** @test */
    public function denormalize_singleSuccessfulResponseAndNoClassMap_responseWithAsIsResultReturned(): void
    {
        $denormalizer = new ResponseObjectDenormalizer();
        $serializedResponse = [
            'jsonrpc' => '2.0',
            'id'      => 'idValue',
            'result'  => [
                'key' => 'value',
            ],
        ];

        $response = $denormalizer->denormalize($serializedResponse, ResponseObject::class);

        $this->assertSame('2.0', $response->jsonrpc);
        $this->assertSame('idValue', $response->id);
        $this->assertSame(['key' => 'value'], $response->result);
        $this->assertNull($response->error);
    }

    /** @test */
    public function denormalize_singleSuccessfulResponseAndClassMap_responseWithDenormalizedResultReturned(): void
    {
        $denormalizer = new ResponseObjectDenormalizer();
        $denormalizer->setDenormalizer($this->denormalizer);
        $request = new RequestObject('method', null);
        $serializedResponse = [
            'jsonrpc' => '2.0',
            'id'      => 'idValue',
            'result'  => [
                'key' => 'value',
            ],
        ];
        $context = [
            'json_rpc' => [
                'request'          => $request,
                'types_by_methods' => [
                    'method' => 'denormalization_type',
                ],
            ],
        ];
        $result = $this->givenDenormalizedObject();

        $response = $denormalizer->denormalize($serializedResponse, ResponseObject::class, self::FORMAT, $context);

        $this->assertDataOfTypeWasDenormalizedWithContext(['key' => 'value'], 'denormalization_type', $context);
        $this->assertSame('2.0', $response->jsonrpc);
        $this->assertSame('idValue', $response->id);
        $this->assertSame($result, $response->result);
        $this->assertNull($response->error);
    }

    /** @test */
    public function denormalize_singleErrorResponseAndNoErrorType_responseWithErrorAndDataAsIsReturned(): void
    {
        $denormalizer = new ResponseObjectDenormalizer();
        $denormalizer->setDenormalizer($this->denormalizer);
        $serializedResponse = [
            'jsonrpc' => '2.0',
            'id'      => 'idValue',
            'error'   => [
                'code'    => 1,
                'message' => 'errorMessage',
                'data'    => [
                    'errorKey' => 'errorValue',
                ],
            ],
        ];
        $context = [
            'json_rpc' => [
                'error_type' => 'denormalization_type',
            ],
        ];
        $errorData = $this->givenDenormalizedObject();

        $response = $denormalizer->denormalize($serializedResponse, ResponseObject::class, self::FORMAT, $context);

        $this->assertDataOfTypeWasDenormalizedWithContext(['errorKey' => 'errorValue'], 'denormalization_type', $context);
        $this->assertSame('2.0', $response->jsonrpc);
        $this->assertNull($response->result);
        $this->assertSame('idValue', $response->id);
        $this->assertInstanceOf(ErrorObject::class, $response->error);
        $this->assertSame(1, $response->error->code);
        $this->assertSame('errorMessage', $response->error->message);
        $this->assertSame($errorData, $response->error->data);
    }

    /** @test */
    public function denormalize_singleErrorResponseAndErrorType_responseWithErrorAndDenormalizedDataReturned(): void
    {
        $denormalizer = new ResponseObjectDenormalizer();
        $denormalizer->setDenormalizer($this->denormalizer);
        $serializedResponse = [
            'jsonrpc' => '2.0',
            'id'      => 'idValue',
            'error'   => [
                'code'    => 1,
                'message' => 'errorMessage',
                'data'    => [
                    'errorKey' => 'errorValue',
                ],
            ],
        ];

        $response = $denormalizer->denormalize($serializedResponse, ResponseObject::class);

        $this->assertSame('2.0', $response->jsonrpc);
        $this->assertNull($response->result);
        $this->assertSame('idValue', $response->id);
        $this->assertInstanceOf(ErrorObject::class, $response->error);
        $this->assertSame(1, $response->error->code);
        $this->assertSame('errorMessage', $response->error->message);
        $this->assertSame(['errorKey' => 'errorValue'], $response->error->data);
    }

    /** @test */
    public function denormalize_notAnObjectResponse_exceptionThrown(): void
    {
        $denormalizer = new ResponseObjectDenormalizer();

        $this->expectException(UnexpectedValueException::class);

        $denormalizer->denormalize('invalid', []);
    }

    private function assertDataOfTypeWasDenormalizedWithContext(array $data, string $type, array $context): void
    {
        \Phake::verify($this->denormalizer)
            ->denormalize($data, $type, self::FORMAT, $context);
    }

    private function givenDenormalizedObject(): object
    {
        $result = new \stdClass();

        \Phake::when($this->denormalizer)
            ->denormalize(\Phake::anyParameters())
            ->thenReturn($result);

        return $result;
    }
}
