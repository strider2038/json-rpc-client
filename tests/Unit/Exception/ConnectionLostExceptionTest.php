<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Exception\ConnectionLostException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ConnectionLostExceptionTest extends TestCase
{
    /** @test */
    public function construct_urlAndReason_formattedMessage(): void
    {
        $exception = new ConnectionLostException('url', 'reason');

        $this->assertSame('Connection "url" was lost: reason.', $exception->getMessage());
    }
}
