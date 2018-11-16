<?php

namespace PiedWeb\CMSBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use PiedWeb\RenderAttributes\AttributesTrait;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    use AttributesTrait;

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

    public function getFunctions()
    {
        return array(
            new TwigFunction('jslink', array(AppExtension::class, 'renderJavascriptLink')),
        );
    }

    public function checkPath($path)
    {
        if (null !== $this->defaultLocale // maybe it's not an i18n
            && $this->defaultLocale != $this->request->getLocale()) {
            $path = rtrim($path, '/').'/'.$this->request->getLocale().'/';
        }

        return $path;
    }

    public static function renderJavascriptLink($anchor, $path, $attr = [])
    {
        return '<span'.self::mergeAndMapAttributes($attr, ['data-href'=>$path]).'>'.$anchor.'</span>';
    }
}
