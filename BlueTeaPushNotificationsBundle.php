<?php

namespace Bluetea\PushNotificationsBundle;

use Bluetea\JiraRestApiBundle\DependencyInjection\Compiler\EndpointCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BlueTeaPushNotificationsBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EndpointCompilerPass());
    }
}
