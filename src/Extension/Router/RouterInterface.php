<?php

namespace PiedWeb\CMSBundle\Extension\Router;

interface RouterInterface
{
    const PATH = 'piedweb_cms_page';

    const CUSTOM_HOST_PATH = 'custom_host_piedweb_cms_page';

    public function generatePathForHomePage($page = null): string;

    public function generatePathForPage($slug = 'homepage'): string;
}
