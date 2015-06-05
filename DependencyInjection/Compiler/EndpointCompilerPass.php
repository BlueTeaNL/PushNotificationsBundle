<?php

namespace Bluetea\PushNotificationsBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EndpointCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Get the configuration
        $config = $container->getExtensionConfig('bluetea_push_notifications')[0];

        // Check if api's are defined
        if (!isset($config['api_client']) || (!isset($config['notification_service']))) {
            throw new InvalidConfigurationException('Configure at least one API service');
        }

        // Check if authentications are defined
        if (!isset($config['authentication']) || (!isset($config['authentication']['appcelerator']) && !isset($config['authentication']['onesignal']))) {
            throw new InvalidConfigurationException('Configure at least one authentication');
        }

        if (isset($config['authentication']['appcelerator'])) {
            $this->createAuthentication($container, $config['authentication']['appcelerator'], 'appcelerator');
        }
        if (isset($config['authentication']['onesignal'])) {
            $this->createAuthentication($container, $config['authentication']['onesignal'], 'onesignal');
        }

        // Initialize the api client
        if (!isset($config['api_client'])) {
            $config['api_client'] = 'guzzle';
        }
        if ($config['notification_service'] == 'appcelerator') {
            if (!isset($config['appcelerator']['base_url']) || !isset($config['appcelerator']['app_id'])) {
                throw new InvalidConfigurationException('Configure appcelerator with a base url and app id');
            }
            $this->createApiClient($container, $config['api_client'], $config['appcelerator']['base_url'], 'appcelerator', $container->getParameter('bluetea_push_notifications.cookieFile'));
            $this->initializeEndpoints($container, $config['notification_service'], $config['appcelerator']['app_id'], null, $container->getParameter('bluetea_push_notifications.appcelerator.notification'));
        } elseif ($config['notification_service'] == 'onesignal') {
            if (!isset($config['onesignal']['app_id']) || !isset($config['onesignal']['base_url']) || !isset($config['onesignal']['rest_api_key'])) {
                throw new InvalidConfigurationException('Configure onesignal with a base url, app id and rest api key');
            }
            $this->createApiClient($container, $config['api_client'], $config['onesignal']['base_url'], 'onesignal', $container->getParameter('bluetea_push_notifications.cookieFile'));
            $this->initializeEndpoints($container, $config['notification_service'], $config['onesignal']['app_id'], $config['onesignal']['rest_api_key'], $container->getParameter('bluetea_push_notifications.onesignal.notification'));
        }
    }

    /**
     * Create authentication services
     *
     * @param ContainerBuilder $container
     * @param $authentication
     * @param $type
     * @throws \LogicException
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    protected function createAuthentication(ContainerBuilder $container, $authentication, $type)
    {
        if ($authentication['type'] == 'basic' && (!isset($authentication['username']) || !isset($authentication['password']))) {
            throw new \LogicException('Username and password are mandatory if using the basic authentication');
        }

        if ($authentication['type'] == 'basic') {
            // Create an authentication service
            $authenticationDefinition = new Definition(
                'Bluetea\PushNotifications\Authentication\BasicAuthentication',
                array('username' => $authentication['username'], 'password' => $authentication['password'])
            );
        } elseif ($authentication['type'] == 'anonymous') {
            // Create an authentication service
            $authenticationDefinition = new Definition(
                'Bluetea\PushNotifications\Authentication\AnonymousAuthentication'
            );
        } else {
            throw new InvalidConfigurationException('Invalid authentication');
        }

        $container->setDefinition(sprintf('push_notifications.%s_authentication', $type), $authenticationDefinition);
    }

    /**
     * Create API client
     *
     * @param ContainerBuilder $container
     * @param $apiClient
     * @param $baseUrl
     * @param $type
     * @param $cookieFile
     * @throws \LogicException
     */
    protected function createApiClient(ContainerBuilder $container, $apiClient, $baseUrl, $type, $cookieFile)
    {
        if ($apiClient == 'guzzle') {
            // Create an API client service
            $apiClientDefinition = new Definition(
                'Bluetea\PushNotifications\Client\GuzzleClient',
                [
                    $baseUrl,
                    new Reference(sprintf('push_notifications.%s_authentication', $type))
                ]
            );
            $apiClientDefinition->addMethodCall('setContentType', array('application/json'));
            $apiClientDefinition->addMethodCall('setAccept', array('application/json'));
            if (isset($cookieFile)) {
                $apiClientDefinition->addMethodCall('setCookieFile', array($cookieFile));
            }
        } else {
            throw new \LogicException('Invalid api client');
        }
        $container->setDefinition(sprintf('push_notifications.%s_api_client', $type), $apiClientDefinition);
    }

    /**
     * Initialize API endpoints
     *
     * @param ContainerBuilder $container
     * @param $availableApi
     * @param $appId
     * @param $restApiKey
     * @param $config
     */
    protected function initializeEndpoints(ContainerBuilder $container, $availableApi, $appId, $restApiKey, $config)
    {
        // Add the appcelerator api client to the push_notifications endpoints
        if ($availableApi == 'appcelerator') {
            $taggedEndpoints = $container->findTaggedServiceIds('push_notifications.appcelerator_endpoint');
            foreach ($taggedEndpoints as $serviceId => $attributes) {
                $endpoint = $container->getDefinition($serviceId);
                // Override the arguments to prevent errors
                $endpoint->setArguments([
                    new Reference('push_notifications.appcelerator_api_client'),
                    $appId,
                    $config
                ]);

                $serviceArray = explode('.', $serviceId);
                $container->setDefinition(sprintf('push_notifications.endpoint.%s', end($serviceArray)), $endpoint);
            }
        } elseif ($availableApi == 'onesignal') {
        // Add the onesignal api client to the push_notifications endpoint
            $taggedEndpoints = $container->findTaggedServiceIds('push_notifications.onesignal_endpoint');
            foreach ($taggedEndpoints as $serviceId => $attributes) {
                $endpoint = $container->getDefinition($serviceId);
                // Override the arguments to prevent errors
                $endpoint->setArguments([
                    new Reference('push_notifications.onesignal_api_client'),
                    $appId,
                    $restApiKey,
                    $config
                ]);

                $serviceArray = explode('.', $serviceId);
                $container->setDefinition(sprintf('push_notifications.endpoint.%s', end($serviceArray)), $endpoint);
            }
        }
    }
}