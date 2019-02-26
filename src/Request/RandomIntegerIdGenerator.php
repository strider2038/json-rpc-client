<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Request;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RandomIntegerIdGenerator implements IdGeneratorInterface
{
    public function generateId(): int
    {
        return random_int(1, PHP_INT_MAX);
    }
}
