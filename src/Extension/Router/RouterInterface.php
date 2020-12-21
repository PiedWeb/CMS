<?php

namespace PiedWeb\CMSBundle\Extension\Router;

use PiedWeb\CMSBundle\Entity\PageInterface;
use Symfony\Component\Routing\RouterInterface as SfRouterInterface;

interface RouterInterface
{
    const PATH = 'piedweb_cms_page';

    const CUSTOM_HOST_PATH = 'custom_host_piedweb_cms_page';

    public function generatePathForHomePage(?PageInterface $page = null): string;

    /**
     * @param string|PageInterface $slug
     */
    public function generate($slug = 'homepage'): string;

    public function setUseCustomHostPath($useCustomHostPath);

    public function getRouter(): SfRouterInterface;
}
