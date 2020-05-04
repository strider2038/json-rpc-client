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

use Strider2038\JsonRpcClient\ClientFactoryInterface;
use Strider2038\JsonRpcClient\ClientInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class JsonRpcClientExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $frameworkConfiguration = $container->getExtensionConfig('framework');

        if (empty($frameworkConfiguration)) {
            return;
        }

        if (!isset($frameworkConfiguration['serializer']['enabled'])) {
            $container->prependExtensionConfig('framework', ['serializer' => ['enabled' => true]]);
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->loadServices($container);
        $configuration = $this->loadConfiguration($configs);

        foreach ($configuration as $clientId => $clientConfig) {
            $definition = new Definition(ClientInterface::class);
            $definition->setPublic(true);
            $definition->setFactory([
                new Reference(ClientFactoryInterface::class),
                'createClient',
            ]);
            $definition->setArgument('$url', $clientConfig['url']);
            $definition->setArgument('$options', $clientConfig['options'] ?? []);

            $container->setDefinition('json_rpc_client.'.$clientId, $definition);
        }

        if (array_key_exists('default', $configuration)) {
            $container->setAlias(ClientInterface::class, 'json_rpc_client.default');
        }
    }

    private function loadServices(ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    private function loadConfiguration(array $configs): array
    {
        $configuration = new Configuration();

        return $this->processConfiguration($configuration, $configs);
    }
}
