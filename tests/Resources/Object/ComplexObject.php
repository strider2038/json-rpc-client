<?php
/*
 * This file is part of json-rpc-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Resources\Object;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ComplexObject
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var array */
    private $meta;

    public function __construct(int $id, string $name, array $meta = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->meta = $meta;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
