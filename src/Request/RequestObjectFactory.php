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
class RequestObjectFactory
{
    /** @var IdGeneratorInterface */
    private $idGenerator;

    public function __construct(IdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function createRequest(string $method, $params): RequestObject
    {
        $object = new RequestObject($method, $params);
        $object->id = $this->idGenerator->generateId();

        return $object;
    }

    public function createNotification(string $method, $params): NotificationObject
    {
        return new NotificationObject($method, $params);
    }
}
