<?php

namespace PiedWeb\CMSBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

class PiedWebCMSExtension extends Extension //implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Better idea to get config everywhere ?
        foreach ($config as $key => $value) {
            $container->setParameter('app.'.$key, $value);
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        // todo : https://symfony.com/doc/current/bundles/extension.html#adding-classes-to-compile
    }

    public function getAlias()
    {
        return 'piedweb_cms';
    }

    /*
    public function prepend(ContainerBuilder $container)
    {
        // Load configurations for other package
        $parser = new Parser();
        $finder = Finder::create()->files()->name('*.yaml')->in(__DIR__.'/../Resources/config/packages/');
        foreach ($finder as $file) {
            $configs = $parser->parse(file_get_contents($file->getRealPath()));
            foreach ($configs as $name => $config) {
                $container->prependExtensionConfig($name, $config);
            }
        }
    }
    /**/
}
