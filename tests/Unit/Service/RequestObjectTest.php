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

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Service\RequestObject;

class RequestObjectTest extends TestCase
{
    private const METHOD = 'method';
    private const PARAMS = 'params';
    private const ID = 1;

    /** @test */
    public function createRequest_methodAndParams_objectCreated(): void
    {
        $object = RequestObject::createRequest(self::METHOD, self::PARAMS);

        $this->assertSame(self::METHOD, $object->method);
        $this->assertSame(self::PARAMS, $object->params);
        $this->assertNull($object->id);
        $this->assertFalse($object->isNotification);
    }

    /** @test */
    public function createNotification_methodAndParams_objectCreated(): void
    {
        $object = RequestObject::createNotification(self::METHOD, self::PARAMS);

        $this->assertSame(self::METHOD, $object->method);
        $this->assertSame(self::PARAMS, $object->params);
        $this->assertNull($object->id);
        $this->assertTrue($object->isNotification);
    }

    /** @test */
    public function jsonSerialize_requestObject_requestElementsReturned(): void
    {
        $object = RequestObject::createRequest(self::METHOD, self::PARAMS);
        $object->id = self::ID;

        $serialized = $object->jsonSerialize();

        $this->assertSame(
            [
                'jsonrpc' => '2.0',
                'method' => self::METHOD,
                'params' => self::PARAMS,
                'id' => self::ID,
            ],
            $serialized
        );
    }
}
