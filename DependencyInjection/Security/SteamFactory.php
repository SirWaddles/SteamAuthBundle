<?php

namespace SteamAuthBundle\DependencyInjection\Security;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class SteamFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.steam.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('steam.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.steam.'.$id;
        $listener = $container
            ->setDefinition($listenerId, new DefinitionDecorator('steam.security.authentication.listener'))
            ->replaceArgument(0, $config['default_route'])
            ->addMethodCall('setProviderKey', [$id])
            ->addTag('security.remember_me_aware', ['id' => $id, 'provider' => $userProvider])
        ;

        $entryPointId = 'security.authentication.entry_point.steam.' . $id;
        $container->setDefinition($entryPointId, new DefinitionDecorator('steam.security.authentication.entry_point'));

        return array($providerId, $listenerId, $entryPointId);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'steam';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $builder = $node->children();
        $builder
            ->scalarNode('default_route')->end()
        ;
    }
}
