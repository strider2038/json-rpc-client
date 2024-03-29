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
class SequentialIntegerIdGenerator implements IdGeneratorInterface
{
    private int $currentId = 1;

    public function generateId(): int
    {
        return $this->currentId++;
    }
}
