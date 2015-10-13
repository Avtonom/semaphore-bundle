<?php

namespace Avtonom\SemaphoreBundle;

use Avtonom\SemaphoreBundle\DependencyInjection\Compiler\AdapterPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AvtonomSemaphoreBundle
 * @package Avtonom\SemaphoreBundle
 */
class AvtonomSemaphoreBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AdapterPass(), PassConfig::TYPE_OPTIMIZE);
    }
}