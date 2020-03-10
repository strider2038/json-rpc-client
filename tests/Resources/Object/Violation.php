<?php
/*
 * This file is part of json-rpc-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Resources\Object;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Violation
{
    /** @var string */
    public $propertyPath;

    /** @var string */
    public $message;
}
