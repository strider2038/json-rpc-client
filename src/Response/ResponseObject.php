<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Response;

use Strider2038\JsonRpcClient\Exception\LogicException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseObject implements ResponseObjectInterface
{
    private string $jsonrpc;

    /** @var mixed */
    private $result;

    private ?ErrorObject $error = null;

    /** @var string|int|null */
    private $id;

    public function __construct(string $protocol, $result, $id)
    {
        $this->jsonrpc = $protocol;
        $this->result = $result;
        $this->id = $id;
    }

    public function getProtocol(): string
    {
        return $this->jsonrpc;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function hasError(): bool
    {
        return null !== $this->error;
    }

    public function getError(): ErrorObject
    {
        if (null === $this->error) {
            throw new LogicException(
                'There is no error in response. Please, use hasError() method to check response for errors.'
            );
        }

        return $this->error;
    }

    public function setError(ErrorObject $error): void
    {
        $this->error = $error;
    }
}
