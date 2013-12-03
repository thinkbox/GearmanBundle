<?php

namespace Riland\GearmanBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WorkerCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $gearmanService = $container->getDefinition('riland.gearman');
        foreach ($container->findTaggedServiceIds('riland.gearman.worker') as $serviceId => $tagsAttributes) {
            $gearmanWorker = $container->getDefinition($serviceId);
            $gearmanWorker->addMethodCall('setEntityManager', array(new Reference('doctrine.orm.gearman_entity_manager')));
            $gearmanWorker->addMethodCall('setGearman', array(new Reference('riland.gearman')));
            foreach ($tagsAttributes as $attributes) {
                $gearmanService->addMethodCall('addWorker', array(new Reference($serviceId), $attributes));
            }
        }
    }

}


