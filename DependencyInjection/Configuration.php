<?php

namespace Lsw\VersionInformationBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lsw_version_information');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
            ->scalarNode('root_dir')->defaultValue(false)->end()
            ->arrayNode('settings')->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('show_icon')->defaultValue(true)->end()
                    ->scalarNode('show_current_branch')->defaultValue(true)->end()
                    ->scalarNode('show_latest_revision')->defaultValue(false)->end()
                    ->scalarNode('show_dirty_files')->defaultValue(true)->end()
                ->end()
            ->end()
            ->arrayNode('collectors')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function($value) { return array('class' => $value); })
                    ->end()
                    ->children()
                        ->scalarNode('class')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}