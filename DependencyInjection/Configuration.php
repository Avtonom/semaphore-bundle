<?php
namespace Avtonom\SemaphoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('avtonom_semaphore')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('adapter')->defaultValue('avtonom_semaphore.adapter.redis')->end()
                ->scalarNode('adapter_redis_client')->defaultValue(null)->end()

                ->scalarNode('key_storage_class')->defaultValue('Avtonom\SemaphoreBundle\Model\SemaphoreKeyStorageBase')->end()

                ->scalarNode('manager_class')->defaultValue('Avtonom\SemaphoreBundle\Model\SemaphoreManager')->end()

                ->scalarNode('is_exception_repeat_block_key')->defaultValue(true)->end()
                ->scalarNode('use_extended_methods')->defaultValue(true)->end()
            ->end();

        return $treeBuilder;
    }
}
