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
    /** @var IdGeneratorInterface */
    private $idGenerator;

    public function __construct(IdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    /**
     * @param string $method
     * @param $params
     * @return RequestObject
     * @throws InvalidRequestParamsException
     */
    public function createRequest(string $method, $params): RequestObject
    {
        $this->validateParams($params);
        $object = new RequestObject($method, $params);
        $object->id = $this->idGenerator->generateId();

        return $object;
    }

    /**
     * @param string $method
     * @param $params
     * @return NotificationObject
     * @throws InvalidRequestParamsException
     */
    public function createNotification(string $method, $params): NotificationObject
    {
        $this->validateParams($params);

        return new NotificationObject($method, $params);
    }

    private function validateParams($params): void
    {
        if (!is_object($params) && !is_array($params)) {
            throw new InvalidRequestParamsException($params);
        }
    }
}
