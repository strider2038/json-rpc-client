<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Exception;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ConnectionFailedException extends \Exception implements JsonRpcClientExceptionInterface
{
    public function __construct(string $url, string $reason)
    {
        parent::__construct(
            sprintf('Unable to establish a connection "%s" because of error %s.', $url, $reason)
        );
    }
}
