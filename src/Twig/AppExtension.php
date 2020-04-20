<?php

namespace PiedWeb\CMSBundle\Twig;

use Cocur\Slugify\Slugify;
use PiedWeb\CMSBundle\Entity\Media;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Service\PageCanonicalService;
use PiedWeb\RenderAttributes\AttributesTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_Environment;

class AppExtension extends AbstractExtension
{
    use AttributesTrait;

    /** @var PageCanonicalService */
    protected $pageCanonical;

    public function __construct(PageCanonicalService $pageCanonical)
    {
        $this->pageCanonical = $pageCanonical;
    }

    public function getFilters()
    {
        return [
            //new TwigFilter('markdown', [AppExtension::class, 'markdownToHtml'], ['is_safe' => ['all']]),
            new TwigFilter('html_entity_decode', 'html_entity_decode'),
            new TwigFilter(
                'punctuation_beautifer',
                [AppExtension::class, 'punctuationBeautifer'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public static function convertMarkdownImage(string $body)
    {
        return preg_replace(
            '/(?:!\[(.*?)\]\((.*?)\))/',
            '{%'
            .PHP_EOL.'    include "@PiedWebCMS/component/_inline_image.html.twig" with {'
            .PHP_EOL.'        "image_src" : "$2",'
            .PHP_EOL.'        "image_alt" : "$1"'
            .PHP_EOL.'    } only'
            .PHP_EOL.'%}'.PHP_EOL,
            $body
        );
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
                'bookmark', // = anchor
                [AppExtension::class, 'renderTxtAnchor'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'anchor', // = bookmark
                [AppExtension::class, 'renderTxtAnchor'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'mail', // = bookmark
                [AppExtension::class, 'renderMail'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'isCurrentPage', // = bookmark
                [$this, 'isCurrentPage'],
                ['is_safe' => ['html'], 'needs_environment' => false]
            ),
            new TwigFunction(
                'gallery',
                [$this, 'renderGallery'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction('isInternalImage', [AppExtension::class, 'isInternalImage']),
            new TwigFunction('getImageFrom', [AppExtension::class, 'transformInlineImageToMedia']),
        ];
    }

    public static function isInternalImage(string $media): bool
    {
        return 0 === strpos($media, '/media/default/');
    }

    public static function transformInlineImageToMedia(string $src)
    {
        $src = substr($src, strlen('/media/default/'));

        $media = new Media();
        $media->setRelativeDir('/media');
        $media->setMedia($src);
        $media->setSlug(preg_replace('@(\.jpg|\.jpeg|\.png|\.gif)$@', '', $src), true);

        return $media;
    }

    public function renderGallery(Twig_Environment $env, Page $currentPage, $filterImageFrom = 1, $filterImageTo = 1001)
    {
        return $env->render('@PiedWebCMS/page/_gallery.html.twig', [
            'page' => $currentPage,
            'galleryFilterFrom' => $filterImageFrom - 1,
            'galleryFilterTo' => $filterImageTo - 1,
        ]);
    }

    public function isCurrentPage(string $uri, ?Page $currentPage)
    {
        return
            null === $currentPage || $uri != $this->pageCanonical->generatePathForPage($currentPage->getRealSlug())
            ? false
            : true;
    }

    public static function renderTxtAnchor(Twig_Environment $env, $name)
    {
        $slugify = new Slugify();
        $name = $slugify->slugify($name);

        return $env->render('@PiedWebCMS/component/_txt_anchor.html.twig', ['name' => $name]);
    }

    public static function renderMail(Twig_Environment $env, $mail, $class = '')
    {
        $mail = str_rot13($mail);

        return $env->render('@PiedWebCMS/component/_mail.html.twig', ['mail' => $mail, 'class' => $class]);
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

        return $env->render(
            '@PiedWebCMS/component/_javascript_link.html.twig',
            [
            'attr' => self::mergeAndMapAttributes($attr, ['class' => 'a', 'data-rot' => str_rot13($path)]),
            'anchor' => $anchor,
            ]
        );
    }
}
