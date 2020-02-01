<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Serialization;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Request\RequestObject;
use Strider2038\JsonRpcClient\Serialization\ContextGenerator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ContextGeneratorTest extends TestCase
{
    /** @test  */
    public function createSerializationContext_singleRequest_requestInContext(): void
    {
        $generator = new ContextGenerator();
        $request = new RequestObject('method', ['params']);

        $context = $generator->createSerializationContext($request);

        $this->assertArrayHasKey('json_rpc', $context);
        $this->assertArrayHasKey('request', $context['json_rpc']);
        $this->assertSame($request, $context['json_rpc']['request']);
    }

    /** @test  */
    public function createSerializationContext_batchRequest_requestsInContext(): void
    {
        $generator = new ContextGenerator();
        $request1 = new RequestObject('method1', ['params']);
        $request1->id = 'id-1';
        $request2 = new RequestObject('method2', ['params']);
        $request2->id = 'id-2';
        $requests = [$request1, $request2];

        $context = $generator->createSerializationContext($requests);

        $this->assertArrayHasKey('json_rpc', $context);
        $this->assertArrayHasKey('requests', $context['json_rpc']);
        $this->assertArrayHasKey('id-1', $context['json_rpc']['requests']);
        $this->assertArrayHasKey('id-2', $context['json_rpc']['requests']);
        $this->assertSame($request1, $context['json_rpc']['requests']['id-1']);
        $this->assertSame($request2, $context['json_rpc']['requests']['id-2']);
    }
}
