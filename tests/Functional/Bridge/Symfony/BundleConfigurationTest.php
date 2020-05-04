<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Tests\Functional\Bridge\Symfony;

use PHPUnit\Framework\TestCase;
use Strider2038\JsonRpcClient\Bridge\Symfony\DependencyInjection\JsonRpcClientExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class BundleConfigurationTest extends TestCase
{
    /**
     * @test
     * @dataProvider validConfigurationProvider
     */
    public function load_validConfiguration_noExceptions(array $configuration): void
    {
        $extension = new JsonRpcClientExtension();
        $builder = new ContainerBuilder();

        $extension->load([$configuration], $builder);

        $this->assertTrue(true);
    }

    public function validConfigurationProvider(): \Iterator
    {
        yield 'minimal' => [
            [
                'default' => [
                    'url' => 'tcp://localhost:4000',
                ],
            ],
        ];

        yield 'allParameters' => [
            [
                'default' => [
                    'url'     => 'tcp://localhost:4000',
                    'options' => [
                        'request_timeout_us'         => 1000000,
                        'enable_response_processing' => false,
                        'connection'                 => [
                            'attempt_timeout_us' => 100000,
                            'timeout_multiplier' => 2.0,
                            'max_attempts'       => 5,
                        ],
                        'http_client_type'        => 'symfony',
                        'transport_configuration' => ['any' => ['options']],
                        'serialization'           => [
                            'serializer_type'         => 'symfony',
                            'result_types_by_methods' => [
                                'method' => 'type',
                            ],
                            'default_error_type'     => 'errorType',
                            'error_types_by_methods' => [
                                'method' => 'errorType',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidConfigurationProvider
     */
    public function load_invalidConfiguration_exceptionThrown(array $configuration): void
    {
        $extension = new JsonRpcClientExtension();
        $builder = new ContainerBuilder();

        $this->expectException(InvalidConfigurationException::class);

        $extension->load([$configuration], $builder);
    }

    public function invalidConfigurationProvider(): \Iterator
    {
        yield 'empty' => [[]];

        yield 'noUrl' => [
            [
                'default' => [],
            ],
        ];

        yield 'emptyUrl' => [
            [
                'default' => [
                    'url' => '',
                ],
            ],
        ];

        yield 'invalidHttpClientType' => [
            [
                'default' => [
                    'url'     => 'tcp://localhost:4000',
                    'options' => [
                        'http_client_type' => 'invalid',
                    ],
                ],
            ],
        ];

        yield 'invalidSerializerType' => [
            [
                'default' => [
                    'url'     => 'tcp://localhost:4000',
                    'options' => [
                        'serialization' => [
                            'serializer_type' => 'invalid',
                        ],
                    ],
                ],
            ],
        ];
    }
}
