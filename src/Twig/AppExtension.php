<?php

namespace PiedWeb\CMSBundle\Twig;

use Cocur\Slugify\Slugify;
use PiedWeb\CMSBundle\Service\PageCanonicalService;
use PiedWeb\RenderAttributes\AttributesTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_Environment;

class AppExtension extends AbstractExtension
{
    use AttributesTrait;

    public function __construct(PageCanonicalService $pageCanonical)
    {
        $this->pageCanonical = $pageCanonical;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('html_entity_decode', 'html_entity_decode'),
            new TwigFilter(
                'punctuation_beautifer',
                [AppExtension::class, 'punctuationBeautifer'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('homepage', [$this->pageCanonical, 'generatePathForHomepage']),
            new TwigFunction('page', [$this->pageCanonical, 'generatePathForPage']),
            new TwigFunction('jslink', [AppExtension::class, 'renderJavascriptLink'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction('link', [AppExtension::class, 'renderJavascriptLink'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction('mail', [AppExtension::class, 'renderEncodedMail'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction(
                'bookmark',
                [AppExtension::class, 'renderTxtBookbmark'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    public static function renderTxtBookbmark(Twig_Environment $env, $name)
    {
        $slugify = new Slugify();
        $name = $slugify->slugify($name);

        return $env->render('@PiedWebCMS/component/_txt_bookmark.html.twig', ['name' => $name]);
    }

    public static function renderEncodedMail(Twig_Environment $env, $mail)
    {
        return $env->render('@PiedWebCMS/component/_encoded_mail.html.twig', ['mail' => str_rot13($mail)]);
    }

    public static function punctuationBeautifer($text)
    {
        return str_replace(
            [' ;', ' :', ' ?', ' !', '« ', ' »', '&laquo; ', ' &raquo;'],
            ['&nbsp;;', '&nbsp;:', '&nbsp;?', '&nbsp;!', '«&nbsp;', '&nbsp;»', '&laquo;&nbsp;', '&nbsp;&raquo;'],
            $text
        );
    }

    public static function renderJavascriptLink(Twig_Environment $env, $anchor, $path, $attr = [])
    {
        if (0 === strpos($path, 'http://')) {
            $path = '-'.substr($path, 7);
        } elseif (0 === strpos($path, 'https://')) {
            $path = '_'.substr($path, 8);
        } elseif (0 === strpos($path, 'mailto:')) {
            $path = '@'.substr($path, 7);
        }

        return $env->render('@PiedWebCMS/component/_javascript_link.html.twig', [
            'attr' => self::mergeAndMapAttributes($attr, ['data-rot' => str_rot13($path)]),
            'anchor' => $anchor,
        ]);
    }
}
