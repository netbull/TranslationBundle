<?php

namespace NetBull\TranslationBundle\DependencyInjection;

use NetBull\TranslationBundle\Locale\Cookie\LocaleCookie;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package NetBull\TranslationBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('netbull_translation');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('disable_locale_listeners')->defaultFalse()->end()
                ->booleanNode('disable_vary_header')->defaultFalse()->end()
                ->scalarNode('guessing_excluded_pattern')->defaultNull()->end()
                ->arrayNode('allowed_locales')
                    ->defaultValue(['en'])
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('locale_map')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('guessing_order')
                        ->beforeNormalization()
                            ->ifString()->then(function ($v) { return [$v]; })
                        ->end()
                        ->defaultValue(['cookie'])
                        ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        $this->addCookieSection($rootNode);
        $this->addSessionSection($rootNode);
        $this->addGeoIpSection($rootNode);
        $this->addSwitcherSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addCookieSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('cookie')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('set_on_change')->defaultTrue()->end()
                        ->scalarNode('class')->defaultValue(LocaleCookie::class)->end()
                        ->scalarNode('name')->defaultValue('ntl')->end()
                        ->integerNode('ttl')->defaultValue(86400)->end()
                        ->scalarNode('path')->defaultValue('/')->end()
                        ->scalarNode('domain')->defaultValue(null)->end()
                        ->booleanNode('secure')->defaultFalse()->end()
                        ->booleanNode('httpOnly')->defaultTrue()->end()
                     ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addSessionSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('session')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('variable')->defaultValue('ntl')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addGeoIpSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('geoip')
                    ->children()
                        ->scalarNode('binary')->defaultValue(null)->end()
                        ->arrayNode('country_map')
                            ->useAttributeAsKey('country')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addSwitcherSection(ArrayNodeDefinition $rootNode)
    {
        $validStatusCodes = [300, 301, 302, 303, 307];
        $rootNode
            ->children()
                ->arrayNode('switcher')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('template')->defaultValue('links')->end()
                        ->booleanNode('show_current_locale')->defaultFalse()->end()
                        ->scalarNode('redirect_to_route')->defaultNull()->end()
                        ->scalarNode('redirect_status_code')->defaultValue('302')->end()
                        ->scalarNode('route')->defaultValue('netbull_translation_locale_switcher')->end()
                        ->booleanNode('use_controller')->defaultFalse()->end()
                        ->booleanNode('use_referrer')->defaultTrue()->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) { return is_null($v['redirect_to_route']);})
                            ->thenInvalid('You need to specify a default fallback route for the use_controller configuration')
                        ->ifTrue(function($v) use ($validStatusCodes) { return !in_array(intval($v['redirect_status_code']), $validStatusCodes);})
                            ->thenInvalid(sprintf('Invalid HTTP status code. Available status codes for redirection are:\n\n%s \n\nSee reference for HTTP status codes', implode(', ', $validStatusCodes)))
                    ->end()
                ->end()
            ->end();
    }
}
