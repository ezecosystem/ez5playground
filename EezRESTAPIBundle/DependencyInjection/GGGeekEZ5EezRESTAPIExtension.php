<?php

namespace GGGeek\eZ5Playground\EezRESTAPIBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GGGeekEZ5EezRESTAPIExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('parameters.yml');
    }

    public function prepend( ContainerBuilder $container )
    {
//        $config = Yaml::parse( __DIR__ . '/../Resources/config/ezpublish.yml' );
//        $container->prependExtensionConfig( 'ezpublish', $config );
//
//       $config = Yaml::parse( __DIR__ . '/../Resources/config/ezpage.yml' );
//        $container->prependExtensionConfig( 'ezpublish', $config );
    }
}
