<?php

namespace Riland\GearmanBundle;

use Riland\GearmanBundle\DependencyInjection\Compiler\WorkerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RilandGearmanBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new WorkerCompilerPass());
    }
}