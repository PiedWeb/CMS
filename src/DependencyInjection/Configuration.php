<?php

namespace PiedWeb\CMSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const DEFAULT_TEMPLATE = '@PiedWebCMSBundle';

    /**
     * php bin/console config:dump-reference PiedWebCMSBundle.
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('piedweb_cms');
        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('media_dir_absolute') // NOT USED ??
                        ->defaultValue('%kernel.project_dir%/media')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('locale')->defaultValue('%locale%')->cannotBeEmpty()->end()
                    ->scalarNode('locales')->defaultValue('fr|en')->end()
                    ->scalarNode('dir') // not explicit = public dir/web dir
                        ->defaultValue('%kernel.project_dir%/public')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('name')->defaultValue('PiedWeb.com')->end()
                    ->booleanNode('default_locale_without_prefix')->defaultTrue()->end()
                    ->scalarNode('template')->defaultValue(self::DEFAULT_TEMPLATE)->end()
                    ->scalarNode('entity_page')->defaultValue('App\Entity\Page')->cannotBeEmpty()->end()
                    ->scalarNode('entity_media')->defaultValue('App\Entity\Media')->cannotBeEmpty()->end()
                    ->scalarNode('entity_user')->defaultValue('App\Entity\User')->cannotBeEmpty()->end()
                    ->scalarNode('entity_pagehasmedia')
                        ->defaultValue('App\Entity\PageHasMedia')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('page_update_notification_email')
                        ->info('Adress email to notify when a page is created or updated')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('page_update_notification_interval')
                        ->defaultValue('PT6H')
                        ->info('minIntervalBetweenTwoNotification')
                    ->end()
                    // For Fos User and maybe other bundle
                    ->scalarNode('email_sender')->defaultValue('me@tld.com')->cannotBeEmpty()->end()
                    ->scalarNode('email_sender_name')->defaultValue('PiedWebCMS')->cannotBeEmpty()->end()
                    ->arrayNode('apps')->requiresAtLeastOneElement()
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('base_url')->defaultValue('')->end()
                                // For Static Website Generation : Bug ? If I put it under a new array, it's
                                // not autocomplte with default value
                                ->scalarNode('static_dir')->defaultValue('%kernel.project_dir%/static')->end()
                                ->booleanNode('static_generateForApache')->defaultTrue()->end()
                                ->booleanNode('static_generateForGithubPages')->defaultFalse()->end()
                                ->booleanNode('static_symlinkMedia')->defaultTrue()->end()
                                ->scalarNode('template')->defaultNull()->end()
                                ->scalarNode('name')->defaultValue('PiedWeb.com')->end()
                                ->scalarNode('color')->defaultValue('#1fa67a')->end()
                                //->arrayNode('hosts')->requiresAtLeastOneElement()->scalarPrototype()->beforeNormalization()->castToArray()->end()
                                ->arrayNode('hosts')->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
