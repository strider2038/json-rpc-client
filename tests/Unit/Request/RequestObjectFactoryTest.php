<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Exception\InvalidRequestParamsException;
use Strider2038\JsonRpcClient\Request\IdGeneratorInterface;
use Strider2038\JsonRpcClient\Request\RequestObjectFactory;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RequestObjectFactoryTest extends TestCase
{
    private const METHOD = 'method';
    private const PARAMS = ['params'];

    /** @var IdGeneratorInterface */
    private $idGenerator;

    protected function setUp(): void
    {
        $this->idGenerator = \Phake::mock(IdGeneratorInterface::class);
    }

    /** @test */
    public function createRequest_methodAndParams_objectWithGeneratedIdCreated(): void
    {
        $factory = $this->createRequestObjectFactory();
        $id = $this->givenGeneratedId();

        $object = $factory->createRequest(self::METHOD, self::PARAMS);

        $this->assertIdWasGenerated();
        $this->assertSame('2.0', $object->jsonrpc);
        $this->assertSame(self::METHOD, $object->method);
        $this->assertSame(self::PARAMS, $object->params);
        $this->assertSame($id, $object->id);
    }

    /** @test */
    public function createRequest_methodWithoutParams_objectWithGeneratedIdCreated(): void
    {
        $factory = $this->createRequestObjectFactory();
        $id = $this->givenGeneratedId();

        $object = $factory->createRequest(self::METHOD);

        $this->assertIdWasGenerated();
        $this->assertSame('2.0', $object->jsonrpc);
        $this->assertSame(self::METHOD, $object->method);
        $this->assertNull($object->params);
        $this->assertSame($id, $object->id);
    }

    /** @test */
    public function createNotification_methodAndParams_objectCreated(): void
    {
        $factory = $this->createRequestObjectFactory();

        $object = $factory->createNotification(self::METHOD, self::PARAMS);

        $this->assertSame('2.0', $object->jsonrpc);
        $this->assertSame(self::METHOD, $object->method);
        $this->assertSame(self::PARAMS, $object->params);
    }

    /** @test */
    public function createRequest_methodAndInvalidParams_exceptionThrown(): void
    {
        $factory = $this->createRequestObjectFactory();

        $this->expectException(InvalidRequestParamsException::class);

        $factory->createRequest(self::METHOD, 'invalid');
    }

    /** @test */
    public function createNotification_methodAndInvalidParams_exceptionThrown(): void
    {
        $factory = $this->createRequestObjectFactory();

        $this->expectException(InvalidRequestParamsException::class);

        $factory->createNotification(self::METHOD, 'invalid');
    }

    private function createRequestObjectFactory(): RequestObjectFactory
    {
        return new RequestObjectFactory($this->idGenerator);
    }

    private function assertIdWasGenerated(): void
    {
        \Phake::verify($this->idGenerator)
            ->generateId();
    }

    private function givenGeneratedId(): int
    {
        $id = 1;

        \Phake::when($this->idGenerator)
            ->generateId()
            ->thenReturn($id);

        return $id;
    }
}
