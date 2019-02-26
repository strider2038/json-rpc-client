<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Service;

use Strider2038\JsonRpcClient\Service\IdGeneratorInterface;
use Strider2038\JsonRpcClient\Service\RequestObject;
use Strider2038\JsonRpcClient\Service\RequestObjectFactory;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RequestObjectFactoryTest extends TestCase
{
    private const METHOD = 'method';
    private const PARAMS = 'params';

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
    public function createNotification_methodAndParams_objectCreated(): void
    {
        $factory = $this->createRequestObjectFactory();

        $object = $factory->createNotification(self::METHOD, self::PARAMS);

        $this->assertSame('2.0', $object->jsonrpc);
        $this->assertSame(self::METHOD, $object->method);
        $this->assertSame(self::PARAMS, $object->params);
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
