<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Unit\Transport\Http;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;
use Strider2038\JsonRpcClient\Transport\Http\GuzzleTransport;
use Strider2038\JsonRpcClient\Transport\Http\HttpTransportFactory;
use Strider2038\JsonRpcClient\Transport\Http\HttpTransportTypeInterface;
use Strider2038\JsonRpcClient\Transport\Http\SymfonyTransport;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class HttpTransportFactoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider clientTypeAndExpectedTransportProvider
     */
    public function createTransport_givenHttpClientType_expectedTransportClass(
        string $httpClientType,
        string $expectedTransportClass
    ): void {
        $transportFactory = new HttpTransportFactory();
        $options = GeneralOptions::createFromArray([
            'http_client' => $httpClientType,
        ]);

        $transport = $transportFactory->createTransport('http://localhost', $options);

        $this->assertInstanceOf($expectedTransportClass, $transport);
    }

    public function clientTypeAndExpectedTransportProvider(): \Iterator
    {
        yield [HttpTransportTypeInterface::AUTODETECT, SymfonyTransport::class];
        yield [HttpTransportTypeInterface::SYMFONY, SymfonyTransport::class];
        yield [HttpTransportTypeInterface::GUZZLE, GuzzleTransport::class];
    }
}
