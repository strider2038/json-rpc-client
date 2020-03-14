<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Response;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Exception\JsonRpcClientException;
use Strider2038\JsonRpcClient\Response\ResponseObject;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseObjectTest extends TestCase
{
    /** @test */
    public function getError_noError_exceptionThrown(): void
    {
        $response = new ResponseObject('', '', '');

        $this->expectException(JsonRpcClientException::class);
        $this->expectExceptionMessage('There is no error in response. Please, use hasError() method to check response for errors.');

        $response->getError();
    }
}
