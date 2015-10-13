<?php

namespace Avtonom\SemaphoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdapterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $adapterName = $container->getParameter('avtonom_semaphore.adapter');
        $adapter = $container->findDefinition($adapterName);
        $manager = $container->getDefinition('avtonom_semaphore.manager');
        $manager->replaceArgument(0, $adapter);

        switch($adapterName){
            case 'millwright_semaphore.adapter.redis':
            case 'avtonom_semaphore.adapter.redis':
                $clientName = $container->getParameter('avtonom_semaphore.adapter_redis_client');
                $client = $container->findDefinition($clientName);
                $adapter->replaceArgument(0, $client);
                break;
            case 'millwright_semaphore.adapter.memcached':
                $clientName = $container->getParameter('avtonom_semaphore.adapter_memcached_client');
                $client = $container->findDefinition($clientName);
                $adapter->replaceArgument(0, $client);
                break;
        }
    }
}