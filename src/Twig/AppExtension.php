<?php

namespace PiedWeb\CMSBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends AbstractExtension
{
    protected $request;
    protected $router;

    protected $defaultLocale;

    public function __construct(RequestStack $request, ?string $defaultLocale = null)
    {
        $this->request = $request->getCurrentRequest();
        $this->defaultLocale = $defaultLocale;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('pwi', [$this, 'checkPath']),
        ];
    }

    public function checkPath($path)
    {
        if (null !== $this->defaultLocale // maybe it's not an i18n
            && $this->defaultLocale != $this->request->getLocale()) {
            $path = rtrim($path, '/').'/'.$this->request->getLocale().'/';
        }

        return $path;
    }
}
