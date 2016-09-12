<?php
namespace SteamAuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class SteamAuthExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('steam_auth.steam_key', $config['steam_key']);
        $container->setParameter('steam_auth.user_class', $config['user_class']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $config = [
            'clients' => [
                'steam' => [
                    'base_url' => 'http://steamcommunity.com/openid/',
                ],
                'steam_user' => [
                    'base_url' => 'http://api.steampowered.com/ISteamUser/',
                ],
            ]
        ];

        $container->prependExtensionConfig('guzzle', $config);
    }
}
