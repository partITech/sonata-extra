<?php

namespace Partitech\SonataExtra\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('partitech_sonata_extra');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('page')
                    ->children()
                        ->booleanNode('realtime_preview')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('blog')
                    ->useAttributeAsKey('name')
                    ->variablePrototype()->end()
                ->end()
                ->arrayNode('content_security_policy')
                    ->useAttributeAsKey('name')
                        ->arrayPrototype()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
                ->arrayNode('smart_service')
                ->children()
                ->booleanNode('translate_on_create_page')->defaultFalse()->end()
                ->booleanNode('translate_on_create_translation')->defaultFalse()->end()
                ->booleanNode('seo_proposal_on_article')->defaultFalse()->end()
                ->booleanNode('seo_proposal_on_page')->defaultFalse()->end()

                ->scalarNode('default_provider')->defaultValue('open_ai')->end()
                ->scalarNode('translation_provider')->defaultValue('open_ai')->end()
                ->scalarNode('seo_provider')->defaultValue('open_ai')->end()

            ->arrayNode('providers')
                        ->useAttributeAsKey('name')
                            ->arrayPrototype()
                        ->variablePrototype()->end()  // Autorise des paramètres arbitraires
                        ->end()
                    ->end()
                ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
