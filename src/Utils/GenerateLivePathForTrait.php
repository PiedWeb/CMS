<?php

namespace PiedWeb\CMSBundle\Utils;

use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Extension\Router\RouterInterface;

trait GenerateLivePathForTrait
{
    /**
     * @var RouterInterface
     */
    protected $router;

    protected function generateLivePathFor($host, $route = 'piedweb_cms_page', $params = [])
    {
        if (isset($params['locale'])) {
            $params['_locale'] = $params['locale'].'/';
            unset($params['locale']);
        }

        if ($host instanceof PageInterface) {
            $page = $host;
            $host = $page->getHost();
        }

        if (isset($page)) {
            $params['slug'] = $page->getRealSlug();
        }

        if ($host) {
            $params['host'] = $host;
            $route = 'custom_host_'.$route;
        }

        return $this->router->getRouter()->generate($route, $params);
    }
}
