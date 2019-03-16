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
use Strider2038\JsonRpcClient\Exception\ConnectionFailedException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ConnectionFailedExceptionTest extends TestCase
{
    /** @test */
    public function construct_urlAndReason_formattedMessage(): void
    {
        $exception = new ConnectionFailedException('url', 'reason');

        $this->assertSame('Unable to establish a connection "url" because of error reason.', $exception->getMessage());
    }
}
