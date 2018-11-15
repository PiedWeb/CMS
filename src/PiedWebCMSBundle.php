<?php

namespace PiedWeb\CMSBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Config\FileLocator;
use PiedWeb\CMSBundle\DependencyInjection\PiedWebCMSExtension;

class PiedWebCMSBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new PiedWebCMSExtension();
        }
        return $this->extension;
    }
}
