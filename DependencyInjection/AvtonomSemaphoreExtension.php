<?php
namespace Avtonom\SemaphoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AvtonomSemaphoreExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('adapters.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('avtonom_semaphore.key_storage_class', $config['key_storage_class']);
        $container->setParameter('avtonom_semaphore.adapter_redis_client', $config['adapter_redis_client']);
        $container->setParameter('avtonom_semaphore.manager_class', $config['manager_class']);
        $container->setParameter('avtonom_semaphore.adapter', $config['adapter']);
        $container->setParameter('avtonom_semaphore.is_exception_repeat_block_key', $config['is_exception_repeat_block_key']);
        $container->setParameter('avtonom_semaphore.use_extended_methods', $config['use_extended_methods']);
    }
}
