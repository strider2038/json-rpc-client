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
use Strider2038\JsonRpcClient\Request\SequentialIntegerIdGenerator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SequentialIntegerIdGeneratorTest extends TestCase
{
    /** @test */
    public function generateId_firstCall_oneReturned(): void
    {
        $generator = new SequentialIntegerIdGenerator();

        $value = $generator->generateId();

        $this->assertSame(1, $value);
    }

    /** @test */
    public function generateId_secondCall_twoReturned(): void
    {
        $generator = new SequentialIntegerIdGenerator();
        $generator->generateId();

        $value = $generator->generateId();

        $this->assertSame(2, $value);
    }
}
