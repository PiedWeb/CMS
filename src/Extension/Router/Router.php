<?php

namespace PiedWeb\CMSBundle\Extension\Router;

use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Service\App;
use Symfony\Component\Routing\RouterInterface as SfRouterInterface;

class Router implements RouterInterface
{
    /** @var string */
    protected $defaultLocale;

    /** @var SfRouterInterface */
    protected $router;

    protected $customHost = false; // TODO make it true on special request, same with absolute

    /** @var App */
    protected $app;

    public function __construct(
        SfRouterInterface $router,
        App $app,
        $defaultLocale
    ) {
        $this->defaultLocale = $defaultLocale;
        $this->router = $router;
        $this->app = $app;
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

        return $this->generatePathForPage($slug);
    }

    public function generatePathForPage($slug = 'homepage'): string
    {
        if ($slug instanceof PageInterface) {
            $slug = $slug->getRealSlug();
        } elseif ('homepage' == $slug) {
            $slug = '';
        }

        if ($this->customHost && ! $this->app->isFirstApp()) {
            return $this->router->generate(self::CUSTOM_HOST_PATH, ['slug' => $slug]);
        }

        return $this->router->generate(self::PATH, ['slug' => $slug]);
    }

    /**
     * Set the value of isLive.
     *
     * @param bool $isLive
     *
     * @return self
     */
    public function setCustomHost(bool $customHost)
    {
        $this->customHost = $customHost;

        return $this;
    }
}
