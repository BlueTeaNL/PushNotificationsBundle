<?php

namespace Bluetea\PushNotificationsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BlueteaPushNotificationsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if ($config['notification_service'] == 'appcelerator') {
            $container->setParameter('bluetea_push_notifications.appcelerator.notification', $config['appcelerator']['notification']);
        } elseif ($config['notification_service'] == 'onesignal') {
            $container->setParameter('bluetea_push_notifications.onesignal.notification', $config['onesignal']['notification']);
        }
        $container->setParameter('bluetea_push_notifications.cookieFile', $config['cookieFile']);
        $container->setParameter('bluetea_push_notifications.config', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

}