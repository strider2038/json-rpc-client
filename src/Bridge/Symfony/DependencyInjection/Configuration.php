<?php
/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Strider2038\JsonRpcClient\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('json_rpc_client');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->performNoDeepMerging()
            ->requiresAtLeastOneElement()
            ->arrayPrototype()
                ->children()
                    ->scalarNode('url')
                        ->info('Connection string to JSON RPC server, for example "http://localhost:1234"')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->append($this->addOptionsNode())
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function addOptionsNode(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('options');

        $node
            ->children()
                ->integerNode('request_timeout_us')
                    ->info('Request timeout in microseconds')
                ->end()
                ->booleanNode('enable_response_processing')
                    ->info('If enabled then successful responses will be unpacked and exceptions will be thrown on errors')
                    ->defaultTrue()
                ->end()
                ->append($this->addConnectionNode())
                ->enumNode('http_client_type')
                    ->info('Preferred HTTP client')
                    ->defaultValue('symfony')
                    ->values(['symfony', 'guzzle'])
                ->end()
                ->variableNode('transport_configuration')
                ->end()
                ->append($this->addSerializationNode())
            ->end();

        return $node;
    }

    private function addConnectionNode(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('connection');

        $node
            ->children()
                ->integerNode('attempt_timeout_us')
                    ->info('Reconnection attempt timeout in microseconds. Must be greater than zero.')
                ->end()
                ->floatNode('timeout_multiplier')
                    ->info('Used to increase timeout value with growing reconnection attempts. Must be greater than 1.0.')
                ->end()
                ->integerNode('max_attempts')
                    ->info('Max sequential attempts to reconnect with remote server. Must be greater or equal to 1.')
                ->end()
            ->end();

        return $node;
    }

    private function addSerializationNode(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('serialization');

        $node
            ->children()
                ->enumNode('serializer_type')
                    ->defaultValue('symfony')
                    ->values(['symfony', 'object', 'array'])
                ->end()
                ->arrayNode('result_types_by_methods')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('default_error_type')
                ->end()
                ->arrayNode('error_types_by_methods')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $node;
    }
}
