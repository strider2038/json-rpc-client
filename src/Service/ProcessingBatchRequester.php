<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Service;

use Strider2038\JsonRpcClient\Exception\NoResponseReceivedException;
use Strider2038\JsonRpcClient\Request\RequestObjectInterface;
use Strider2038\JsonRpcClient\Response\ResponseObjectInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ProcessingBatchRequester extends RawBatchRequester
{
    public function send(): array
    {
        /** @var ResponseObjectInterface[] $responses */
        $responses = parent::send();

        /** @var RequestObjectInterface[] $queue */
        $queue = $this->getQueue();

        $orderedResults = [];

        foreach ($queue as $request) {
            if (null === $request->getId()) {
                $orderedResults[] = null;
            } else {
                $matchingResponse = $this->getMatchingResponseForRequest($responses, $request);
                $orderedResults[] = $matchingResponse->getResult();
            }
        }

        return $orderedResults;
    }

    private function getMatchingResponseForRequest(array $responses, RequestObjectInterface $request): ResponseObjectInterface
    {
        $matchingResponse = null;

        foreach ($responses as $response) {
            if ($request->getId() === $response->getId()) {
                $matchingResponse = $response;

                break;
            }
        }

        if (null === $matchingResponse) {
            throw new NoResponseReceivedException($request);
        }

        return $matchingResponse;
    }
}
