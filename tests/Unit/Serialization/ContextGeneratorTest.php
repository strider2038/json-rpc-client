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
    private const ERROR_TYPE = 'errorType';
    private const RESULT_TYPES_BY_METHODS = ['method' => 'type'];

    /** @test  */
    public function createSerializationContext_singleRequest_requestInContext(): void
    {
        $generator = new ContextGenerator();
        $request = new RequestObject('id', 'method', ['params']);

        $context = $generator->createSerializationContext($request);

        $this->assertArrayHasKey('json_rpc', $context);
        $this->assertArrayHasKey('request', $context['json_rpc']);
        $this->assertSame($request, $context['json_rpc']['request']);
    }

    /** @test  */
    public function createSerializationContext_serializationOptions_optionsInContext(): void
    {
        $generator = new ContextGenerator(self::RESULT_TYPES_BY_METHODS, self::ERROR_TYPE);
        $request = new RequestObject('id', 'method', ['params']);

        $context = $generator->createSerializationContext($request);

        $this->assertArrayHasKey('json_rpc', $context);
        $this->assertArrayHasKey('result_types_by_methods', $context['json_rpc']);
        $this->assertArrayHasKey('error_type', $context['json_rpc']);
        $this->assertSame(self::RESULT_TYPES_BY_METHODS, $context['json_rpc']['result_types_by_methods']);
        $this->assertSame(self::ERROR_TYPE, $context['json_rpc']['error_type']);
    }

    /** @test  */
    public function createSerializationContext_batchRequest_requestsInContext(): void
    {
        $generator = new ContextGenerator();
        $request1 = new RequestObject('id-1', 'method1', ['params']);
        $request2 = new RequestObject('id-2', 'method2', ['params']);
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
