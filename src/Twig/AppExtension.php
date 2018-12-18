<?php

namespace PiedWeb\CMSBundle\Twig;

use Twig\Extension\AbstractExtension;
use PiedWeb\RenderAttributes\AttributesTrait;
use PiedWeb\CMSBundle\Service\PageCanonicalService;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    use AttributesTrait;

    public function __construct(PageCanonicalService $pageCanonical)
    {
        $this->pageCanonical = $pageCanonical;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('homepage', [$this->pageCanonical, 'generatePathForHomepage']),
            new TwigFunction('page', [$this->pageCanonical, 'generatePathForPage']),
            new TwigFunction('jslink', [AppExtension::class, 'renderJavascriptLink']),
        ];
    }

    public static function renderJavascriptLink($anchor, $path, $attr = [])
    {
        return '<span'.self::mergeAndMapAttributes($attr, ['data-href' => $path]).'>'.$anchor.'</span>';
    }
}
