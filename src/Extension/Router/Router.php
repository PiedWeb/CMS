<?php

namespace PiedWeb\CMSBundle\Extension\Router;

use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Service\App;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface as SfRouterInterface;

class Router implements RouterInterface
{
    /** @var string */
    protected $defaultLocale;

    /** @var SfRouterInterface */
    protected $router;

    protected $useCustomHostPath = true; // TODO make it true on special request, same with absolute

    /** @var App */
    protected $app;

    /** @var string */
    protected $currentHost;

    public function __construct(
        SfRouterInterface $router,
        App $app,
        RequestStack $requestStack,
        $defaultLocale
    ) {
        $this->defaultLocale = $defaultLocale;
        $this->router = $router;
        $this->app = $app;
        $this->currentHost = $requestStack->getCurrentRequest() ? $requestStack->getCurrentRequest()->getHost() : '';
    }

    /**
     * This function assume you are usin /X for X pages's home
     * and / for YY page home if your default language is YY
     * X/Y may be en/fr/...
     */
    public function generatePathForHomePage($page = null): string
    {
        $slug = '';

        if (null !== $page && $page->getLocale() != $this->defaultLocale) {
            $slug = $page->getLocale();
        }

        return $this->generate($slug);
    }

    public function generate($slug = 'homepage'): string
    {
        if ($slug instanceof PageInterface) {
            $slug = $slug->getRealSlug();
        } elseif ('homepage' == $slug) {
            $slug = '';
        }

        if ($this->mayUseCustomPath()) {
            return $this->router->generate(self::CUSTOM_HOST_PATH, [
                    'host' => $this->app->getCurrentPage()->getHost(),
                'slug' => $slug,
            ]);
        }

        return $this->router->generate(self::PATH, ['slug' => $slug]);
    }

    protected function mayUseCustomPath()
    {
        return $this->useCustomHostPath
            && $this->currentHost // we have a request
            && $this->app->getCurrentPage() // a page is loaded
            && $this->app->getCurrentPage()->getHost()
            && ! $this->app->isMainHost($this->currentHost);
    }

    /**
     * Set the value of isLive.
     *
     * @param bool $isLive
     *
     * @return self
     */
    public function setUseCustomHostPath($useCustomHostPath)
    {
        $this->useCustomHostPath = $useCustomHostPath;

        return $this;
    }

    /**
     * Get the value of router.
     */
    public function getRouter(): SfRouterInterface
    {
        return $this->router;
    }
}
