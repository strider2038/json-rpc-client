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
use Strider2038\JsonRpcClient\Request\RandomIntegerIdGenerator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RandomIntegerIdGeneratorTest extends TestCase
{
    /** @test */
    public function generateId_noParameters_randomIntegerReturned(): void
    {
        $generator = new RandomIntegerIdGenerator();

        $id = $generator->generateId();

        $this->assertGreaterThan(0, $id);
    }
}
