<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Serialization;

use Strider2038\JsonRpcClient\Request\RequestObjectInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ContextGenerator
{
    /** @var string[] */
    private array $resultTypesByMethods;

    private ?string $defaultErrorType;

    /** @var string[] */
    private array $errorTypesByMethods;

    public function __construct(
        array $resultTypesByMethods = [],
        string $errorType = null,
        array $errorTypesByMethods = []
    ) {
        $this->resultTypesByMethods = $resultTypesByMethods;
        $this->defaultErrorType = $errorType;
        $this->errorTypesByMethods = $errorTypesByMethods;
    }

    public function createSerializationContext($request): array
    {
        $context = [
            'result_types_by_methods' => $this->resultTypesByMethods,
            'default_error_type'      => $this->defaultErrorType,
            'error_types_by_methods'  => $this->errorTypesByMethods,
        ];

        if ($request instanceof RequestObjectInterface) {
            $context['request'] = $request;
        } else {
            $requests = [];

            /** @var RequestObjectInterface $singleRequest */
            foreach ($request as $singleRequest) {
                $requests[$singleRequest->getId()] = $singleRequest;
            }

            $context['requests'] = $requests;
        }

        return ['json_rpc' => $context];
    }
}
