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
use Strider2038\JsonRpcClient\Exception\ErrorResponseException;
use Strider2038\JsonRpcClient\Exception\ResponseException;
use Strider2038\JsonRpcClient\Response\ErrorObject;
use Strider2038\JsonRpcClient\Response\ExceptionalResponseValidator;
use Strider2038\JsonRpcClient\Response\ResponseObject;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ExceptionalResponseValidatorTest extends TestCase
{
    /** @test */
    public function validate_null_noExceptions(): void
    {
        $validator = new ExceptionalResponseValidator();

        $validator->validate(null);

        $this->assertTrue(true);
    }

    /** @test */
    public function validate_string_exceptionThrown(): void
    {
        $validator = new ExceptionalResponseValidator();

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Response from server expected to be an object or an array of objects.');

        $validator->validate('string');
    }

    /** @test */
    public function validate_singleResponseWithoutVersion_exceptionThrown(): void
    {
        $validator = new ExceptionalResponseValidator();
        $response = new ResponseObject('', '', '');

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Invalid JSON RPC version in server request.');

        $validator->validate($response);
    }

    /** @test */
    public function validate_singleResponseWithError_exceptionThrown(): void
    {
        $validator = new ExceptionalResponseValidator();
        $response = new ResponseObject('2.0', '', '');
        $response->setError(new ErrorObject(
            123,
            'error message',
            ['key' => 'value']
        ));

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage(
            'Server response has error: code 123, message "error message", data {"key":"value"}.'
        );

        $validator->validate($response);
    }

    /** @test */
    public function validate_responseInBatchWithoutVersion_exceptionThrown(): void
    {
        $validator = new ExceptionalResponseValidator();
        $response = new ResponseObject('', '', '');

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Invalid JSON RPC version in server request.');

        $validator->validate([$response]);
    }

    /** @test */
    public function validate_responseInBatchWithError_exceptionThrown(): void
    {
        $validator = new ExceptionalResponseValidator();
        $response = new ResponseObject('2.0', '', '');
        $response->setError(new ErrorObject(
            123,
            'error message',
            ['key' => 'value']
        ));

        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage(
            'Server response has error: code 123, message "error message", data {"key":"value"}.'
        );

        $validator->validate([$response]);
    }
}
