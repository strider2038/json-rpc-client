<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Integration;

use Strider2038\JsonRpcClient\ClientInterface;
use Strider2038\JsonRpcClient\Tests\TestCase\ClientIntegrationTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SymfonyBundleClientTest extends KernelTestCase
{
    use ClientIntegrationTestCaseTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected function createClient(): ClientInterface
    {
        return KernelTestCase::$container->get(ClientInterface::class);
    }
}
