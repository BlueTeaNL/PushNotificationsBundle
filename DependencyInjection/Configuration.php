<?php

namespace Bluetea\PushNotificationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bluetea_push_notifications', 'array');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('api_client')
                    ->defaultValue('guzzle')
                ->end()
                ->enumNode('notification_service')
                    ->defaultValue('appcelerator')
                    ->values(array('appcelerator', 'onesignal'))
                ->end()
                ->scalarNode('cookieFile')
                    ->defaultValue('pushcookie.txt')
                ->end()
                ->arrayNode('appcelerator')
                    ->children()
                        ->scalarNode('app_id')->end()
                        ->scalarNode('base_url')->end()
                        ->arrayNode('notification')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('channel')->defaultValue('alert')->end()
                                ->scalarNode('badge')->defaultValue(1)->end()
                                ->booleanNode('vibrate')->defaultValue(true)->end()
                                ->scalarNode('sound')->defaultValue('default')->end()
                                ->scalarNode('icon')->defaultValue('appicon')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('onesignal')
                    ->children()
                        ->scalarNode('app_id')->end()
                        ->scalarNode('base_url')->end()
                        ->scalarNode('rest_api_key')->end()
                        ->arrayNode('notification')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('isIos')->defaultValue(true)->end()
                                ->booleanNode('isAndroid')->defaultValue(true)->end()
                                ->booleanNode('isWP')->defaultValue(false)->end()
                                ->scalarNode('included_segments')->defaultValue(['ALL'])->end()
                                ->scalarNode('excluded_segments')->defaultValue([])->end()
                                ->scalarNode('ios_sound')->defaultValue('default.wav')->end()
                                ->scalarNode('android_sound')->defaultValue('default')->end()
                                ->scalarNode('wp_sound')->defaultValue('default.wav')->end()
                                ->scalarNode('small_icon')->defaultValue('appicon')->end()
                                ->scalarNode('ios_badgeType')->defaultValue('None')->end()
								->scalarNode('ios_badgeCount')->defaultValue(1)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('authentication')
                    ->children()
                        ->arrayNode('appcelerator')
                            ->children()
                                ->enumNode('type')
                                    ->defaultValue('basic')
                                    ->values(array('basic', 'anonymous'))
                                ->end()
                                ->scalarNode('username')->end()
                                ->scalarNode('password')->end()
                            ->end()
                        ->end()
                        ->arrayNode('onesignal')
                            ->children()
                                ->enumNode('type')
                                    ->defaultValue('anonymous')
                                    ->values(array('basic', 'anonymous'))
                                ->end()
                                ->scalarNode('username')->end()
                                ->scalarNode('password')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}