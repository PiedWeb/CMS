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
        if (false === $this->isForDefaultLocale($expectedLocale)) {
            return $this->router->generate('localized_piedweb_cms_page', [
                'slug' => '',
                '_locale' => $this->getLocale($expectedLocale),
            ]);
        }

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

        if (null === $this->defaultLocale
            || ($this->isForDefaultLocale($expectedLocale) && $this->defaultLocaleWithoutPrefix)
        ) {
            return $this->router->generate('piedweb_cms_page', ['slug' => $slug]);
        }

        return $this->router->generate('localized_piedweb_cms_page', [
            'slug' => $slug,
            '_locale' => $this->getLocale($expectedLocale),
        ]);
    }

    protected function isForDefaultLocale(?string $expectedLocale = null)
    {
        return null === $this->defaultLocale // maybe it's not an i18n app
            || (null !== $expectedLocale && $this->defaultLocale == $expectedLocale)
            || (
                null === $expectedLocale
                && null !== $this->request->getCurrentRequest()
                && $this->defaultLocale == $this->request->getCurrentRequest()->getLocale()
            )
        ;
    }

    /**
     * Always get a locale even if we are not in a request.
     */
    protected function getLocale(?string $expectedLocale)
    {
        return $expectedLocale ?? (
            null === $this->request->getCurrentRequest()
                ? $this->defaultLocale
                : $this->request->getCurrentRequest()->getLocale()
        );
    }
}
