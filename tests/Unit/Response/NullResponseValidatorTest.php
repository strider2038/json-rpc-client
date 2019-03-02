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
use Strider2038\JsonRpcClient\Response\NullResponseValidator;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class NullResponseValidatorTest extends TestCase
{
    /** @test */
    public function validate_response_noActions(): void
    {
        $validator = new NullResponseValidator();
        $response = \Phake::mock(ResponseObjectInterface::class);

        $validator->validate($response);

        \Phake::verifyNoInteraction($response);
    }
}
