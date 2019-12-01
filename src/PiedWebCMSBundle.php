<?php

namespace PiedWeb\CMSBundle;

use PiedWeb\CMSBundle\DependencyInjection\PiedWebCMSExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

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
