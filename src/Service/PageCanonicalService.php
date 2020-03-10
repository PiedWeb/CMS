<?php

namespace PiedWeb\CMSBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;

class PageCanonicalService
{
    protected $request;
    protected $router;
    protected $defaultLocaleWithoutPrefix;
    protected $defaultLocale;
    protected $locale;
    protected $params;

    public function __construct(
        RequestStack $request,
        Router $router,
        bool $defaultLocaleWithoutPrefix = false,
        ?string $defaultLocale = null
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->defaultLocaleWithoutPrefix = $defaultLocaleWithoutPrefix;
        $this->defaultLocale = $defaultLocale;
    }

    public function generatePathForHomepage(?string $expectedLocale = null)
    {
        return $this->router->generate('piedweb_cms_homepage');
    }

    /**
     * @var string Permit to generate a link for another language than the current request
     */
    public function generatePathForPage(string $slug, ?string $expectedLocale = null)
    {
        if ('' == $slug) {
            return $this->generatePathForHomepage($expectedLocale);
        }

        return $this->router->generate('piedweb_cms_page', ['slug' => $slug]);
    }
}
