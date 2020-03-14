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
use Strider2038\JsonRpcClient\Exception\ErrorResponseException;
use Strider2038\JsonRpcClient\Response\ErrorObject;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ErrorResponseExceptionTest extends TestCase
{
    /** @test */
    public function getError_errorObjectInjected_errorObjectReturned(): void
    {
        $expectedError = new ErrorObject(0, '', '');
        $exception = new ErrorResponseException($expectedError);

        $error = $exception->getError();

        $this->assertSame($expectedError, $error);
    }
}
