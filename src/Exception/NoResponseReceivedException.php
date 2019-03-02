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

use Strider2038\JsonRpcClient\Request\RequestObjectInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class NoResponseReceivedException extends JsonRpcClientException
{
    public function __construct(RequestObjectInterface $request)
    {
        parent::__construct(
            sprintf(
                'Request object "%s" has no response from server in batch request.',
                json_encode($request)
            )
        );
    }
}
