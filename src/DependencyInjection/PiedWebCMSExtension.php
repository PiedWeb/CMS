<?php

namespace PiedWeb\CMSBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class PiedWebCMSBundleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->addAnnotatedClassesToCompile(array(
            '**Bundle\\Controller\\',
        ));
    }
}
