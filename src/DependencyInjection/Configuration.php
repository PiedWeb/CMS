<?php

namespace PiedWeb\CMSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const DEFAULT_TEMPLATE = '@PiedWebCMS';
    const DEFAULT_APP_FALLBACK = [
        'locale',
        'locales',
        'name',
        'hosts',
        'base_url',
        'template',
        'custom_properties',
    ];
    const DEFAULT_CUSTOM_PROPERTIES = [
        'main_content_type' => 'Raw',
        'can_use_twig_shortcode' => true,
        'main_content_shortcode' => 'twig,date,email,encryptedLink,image,phoneNumber,twigVideo,punctuation,markdown',
        'fields_shortcode' => 'twig,date,email,encryptedLink,phoneNumber',
        'assets' => [
            'stylesheets' => [
                '/bundles/piedwebcms/tailwind.css',
            ],
            'javascripts' => ['/bundles/piedwebcms/page.js'],
        ],
    ];
    const DEFAULT_TWIG_SHORTCODE = true;

    /**
     * php bin/console config:dump-reference PiedWebCMSBundle.
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('piedweb_cms');
        $treeBuilder->getRootNode()->children()
            // not explicit => public dir web dir...
            // used in PageScanner,StaticGenerator,MediaWebPathResolver to find public/index.php TODO rplce it
            // for the symfony parameters
            ->scalarNode('dir')->defaultValue('%kernel.project_dir%/public')->cannotBeEmpty()->end()
            ->scalarNode('entity_page')->defaultValue('App\Entity\Page')->cannotBeEmpty()->end()
            ->scalarNode('entity_media')->defaultValue('App\Entity\Media')->cannotBeEmpty()->end()
            ->scalarNode('entity_user')->defaultValue('App\Entity\User')->cannotBeEmpty()->end()
            ->scalarNode('entity_pagehasmedia')->defaultValue('App\Entity\PageHasMedia')->cannotBeEmpty()->end()
            ->scalarNode('media_dir_absolute')->defaultValue('%kernel.project_dir%/media')->cannotBeEmpty()->end()
            ->variableNode('app_fallback_properties')->defaultValue(self::DEFAULT_APP_FALLBACK)->cannotBeEmpty()->end()
            // default app value
            ->scalarNode('locale')->defaultValue('%locale%')->cannotBeEmpty()->end()
            ->scalarNode('locales')->defaultValue('fr|en')->end()
            ->scalarNode('name')->defaultValue('PiedWeb/CMS')->end()
            ->variableNode('host')->defaultValue('localhost')->end()
            ->variableNode('hosts')->defaultValue(['%pwc.host%'])->end()
            ->scalarNode('base_url')->defaultValue('https://%pwc.host%')->end()
            ->scalarNode('template')->defaultValue(self::DEFAULT_TEMPLATE)->cannotBeEmpty()->end()
            ->variableNode('custom_properties')->defaultValue(self::DEFAULT_CUSTOM_PROPERTIES)->end()

            ->variableNode('apps')->end()
        ->end();

        return $treeBuilder;
    }
}
