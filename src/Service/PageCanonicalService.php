<?php

namespace PiedWeb\CMSBundle\Service;

use Symfony\Component\Routing\Router;

class PageCanonicalService
{
    protected $router;
    protected $defaultLocaleWithoutPrefix;
    protected $defaultLocale;
    protected $locale;
    protected $params;

    public function __construct(
        Router $router,
        bool $defaultLocaleWithoutPrefix = false,
        ?string $defaultLocale = null
    ) {
        $this->router = $router;
        $this->defaultLocaleWithoutPrefix = $defaultLocaleWithoutPrefix;
        $this->defaultLocale = $defaultLocale;
    }

    public function generatePathForHomepage()
    {
        return $this->router->generate('piedweb_cms_page', ['slug' => '']);
    }

    /**
     * @var string Permit to generate a link for another language than the current request
     */
    public function generatePathForPage(?string $slug)
    {
        if (null === $slug) {
            return;
        }

        if ('' === $slug) {
            return $this->generatePathForHomepage();
        }

        return $this->router->generate('piedweb_cms_page', ['slug' => $slug]);
    }
}
