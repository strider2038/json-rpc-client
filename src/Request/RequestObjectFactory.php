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

use Strider2038\JsonRpcClient\Exception\InvalidRequestParamsException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RequestObjectFactory
{
    private IdGeneratorInterface $idGenerator;

    public function __construct(IdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    /**
     * @param array|object|null $params
     *
     * @throws InvalidRequestParamsException
     */
    public function createRequest(string $method, $params = null): RequestObject
    {
        $this->validateParams($params);
        $id = $this->idGenerator->generateId();

        return new RequestObject($id, $method, $params);
    }

    /**
     * @param array|object|null $params
     *
     * @throws InvalidRequestParamsException
     */
    public function createNotification(string $method, $params = null): NotificationObject
    {
        $this->validateParams($params);

        return new NotificationObject($method, $params);
    }

    private function validateParams($params): void
    {
        if (null !== $params && !is_object($params) && !is_array($params)) {
            throw new InvalidRequestParamsException($params);
        }
    }
}
