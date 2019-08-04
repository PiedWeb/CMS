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
            new TwigFunction('jslink', [AppExtension::class, 'renderJavascriptLink'], ['is_safe' => ['html']]),
        ];
    }

    public static function renderJavascriptLink($anchor, $path, $attr = [])
    {
        if (0 === strpos($path, 'http://')) {
            $path = '-'.substr($path, 7);
        } elseif (0 === strpos($path, 'https://')) {
            $path = '_'.substr($path, 8);
        } elseif (0 === strpos($path, 'mailto:')) {
            $path = '@'.substr($path, 7);
        }

        return '<span'.self::mergeAndMapAttributes($attr, ['data-rot' => str_rot13($path)]).' rel=nofollow>'.$anchor.'</span>';
    }
}
