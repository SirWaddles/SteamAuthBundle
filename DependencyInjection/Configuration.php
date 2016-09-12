<?php

namespace SteamAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('steam_auth')
            ->children()
                ->scalarNode('steam_key')->end()
                ->scalarNode('user_class')->end()
            ->end()
            ;

        return $treeBuilder;
    }
}
