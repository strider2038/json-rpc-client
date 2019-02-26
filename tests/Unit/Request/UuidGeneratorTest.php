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
use Strider2038\JsonRpcClient\Request\UuidGenerator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class UuidGeneratorTest extends TestCase
{
    /** @test */
    public function generateId_noParameters_uuid4Returned(): void
    {
        $generator = new UuidGenerator();

        $id = $generator->generateId();

        $this->assertRegExp('/^[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}$/', $id);
    }
}
