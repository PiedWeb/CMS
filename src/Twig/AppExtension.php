<?php

namespace PiedWeb\CMSBundle\Twig;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Entity\Media;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Service\PageCanonicalService;
use PiedWeb\RenderAttributes\AttributesTrait;
use Twig\Environment as Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    use AttributesTrait;

    /** @var PageCanonicalService */
    protected $pageCanonical;

    /** @var EntityManagerInterface */
    private $em;

    /** @var string */
    private $page_class;

    public function __construct(EntityManager $em, string $page_class, PageCanonicalService $pageCanonical)
    {
        $this->em = $em;
        $this->pageCanonical = $pageCanonical;
        $this->page_class = $page_class;
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
                'isCurrentPage',
                [$this, 'isCurrentPage'],
                ['is_safe' => ['html'], 'needs_environment' => false]
            ),
            new TwigFunction(
                'gallery',
                [$this, 'renderGallery'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'list',
                [$this, 'renderPagesList'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction('isInternalImage', [AppExtension::class, 'isInternalImage']),
            new TwigFunction('getImageFrom', [AppExtension::class, 'transformInlineImageToMedia']),
        ];
    }

    public function renderPagesList(
        Twig $env,
        string $containing = '',
        int $number = 3,
        string $orderBy = 'createdAt',
        string $template = '@PiedWebCMS/page/_pages_list.html.twig'
    ) {
        $qb = $this->em->getRepository($this->page_class)->getQueryToFindPublished('p');
        $qb->andWhere('p.mainContent LIKE :containing')->setParameter('containing', '%'.$containing.'%');
        $qb->orderBy('p.'.$orderBy, 'DESC');
        $qb->setMaxResults($number);

        $pages = $qb->getQuery()->getResult();

        return $env->render($template, ['pages' => $pages]);
    }

    public static function isInternalImage(string $media): bool
    {
        return 0 === strpos($media, '/media/default/');
    }

    public static function transformInlineImageToMedia(string $src)
    {
        if (self::isInternalImage($src)) {
            $src = substr($src, strlen('/media/default/'));

            $media = new Media();
            $media->setRelativeDir('/media');
            $media->setMedia($src);
            $media->setSlug(preg_replace('@(\.jpg|\.jpeg|\.png|\.gif)$@', '', $src), true);

            return $media;
        }

        $media = new Media();
        $media->setRelativeDir($src);
        $media->setMedia('');
        $media->setSlug('', true);

        return $media;
    }

    public function renderGallery(Twig $env, Page $currentPage, $filterImageFrom = 1, $length = 1001)
    {
        return $env->render('@PiedWebCMS/page/_gallery.html.twig', [
            'page' => $currentPage,
            'galleryFilterFrom' => $filterImageFrom - 1,
            'length' => $length,
        ]);
    }

    public function isCurrentPage(string $uri, ?Page $currentPage)
    {
        return
            null === $currentPage || $uri != $this->pageCanonical->generatePathForPage($currentPage->getRealSlug())
            ? false
            : true;
    }

    public static function renderTxtAnchor(Twig $env, $name)
    {
        $slugify = new Slugify();
        $name = $slugify->slugify($name);

        return $env->render('@PiedWebCMS/component/_txt_anchor.html.twig', ['name' => $name]);
    }

    public static function renderEncodedMail(Twig $env, $mail, $class = '')
    {
        return $env->render('@PiedWebCMS/component/_encoded_mail.html.twig', [
            'mail' => str_rot13($mail),
            'class' => $class,
        ]);
    }

    public static function punctuationBeautifer($text)
    {
        return str_replace(
            [' ;', ' :', ' ?', ' !', '« ', ' »', '&laquo; ', ' &raquo;'],
            ['&nbsp;;', '&nbsp;:', '&nbsp;?', '&nbsp;!', '«&nbsp;', '&nbsp;»', '&laquo;&nbsp;', '&nbsp;&raquo;'],
            $text
        );
    }

    public static function renderJavascriptLink(Twig $env, $anchor, $path, $attr = [])
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
