<?php
/**
 * todo: make it cleaner: https://symfony.com/doc/current/bundles/prepend_extension.html.
 */

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
        // not get config every where and load only what's need
        self::loadConfigHelper($container, $config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        // todo : https://symfony.com/doc/current/bundles/extension.html#adding-classes-to-compile
    }

    /**
     * @param string $prefix must contain the last
     *
     * @return void
     */
    protected static function loadConfigHelper(ContainerBuilder $container, array $config, $prefix = '')
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                self::loadConfigHelper($container, $value, $prefix.$key.'.');
            } else {
                $container->setParameter('app.'.$prefix.$key, $value); // to deprecate in next release
                $container->setParameter('pwc.'.$prefix.$key, $value);
            }
        }
    }

    public function getAlias()
    {
        return 'piedweb_cms'; // change to pwc todo
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
