<?php
/*
 * This file is part of JSON RPC Client.
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
class CreateProductResponse
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var \DateTimeInterface */
    public $productionDate;

    /** @var int */
    public $price;

    /** @var Image[] */
    public $images;
}
