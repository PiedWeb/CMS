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
            new TwigFunction('jslink', [AppExtension::class, 'renderJavascriptLink'], array('is_safe' => array('html'))),
        ];
    }

    public static function renderJavascriptLink($anchor, $path, $attr = [])
    {
        if (strpos($path, 'http://')===0)        $path = '-'.substr($path, 7);
        elseif (strpos($path, 'https://')===0)  $path = '_'.substr($path, 8);
        elseif (strpos($path, 'mailto:')===0)   $path = '@'.substr($path, 7);

        return '<span'.self::mergeAndMapAttributes($attr, ['data-rot' => str_rot13($path)]).'>'.$anchor.'</span>';
    }
}
