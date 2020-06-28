<?php

namespace PiedWeb\CMSBundle\Twig;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PiedWeb\CMSBundle\Entity\Media;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Service\MainContentManager;
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
            new TwigFilter('preg_replace', [AppExtension::class, 'pregReplace']),
            new TwigFilter(
                'punctuation_beautifer',
                [MainContentManager::class, 'punctuationBeautifer'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public static function pregReplace($subject, $pattern, $replacement)
    {
        return preg_replace($pattern, $replacement, $subject);
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
                'video',
                [$this, 'renderVideo'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'encode',
                [AppExtension::class, 'encode'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'list',
                [$this, 'renderPagesList'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction('isInternalImage', [AppExtension::class, 'isInternalImage']),
            new TwigFunction('getImageFrom', [AppExtension::class, 'transformInlineImageToMedia']),
            new TwigFunction('getEmbedCode', [AppExtension::class, 'getEmbedCode']),
            new TwigFunction('extract', [$this, 'extract'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    public function extract(Twig $env, string $name, Page $page)
    {
        $mainContentManager = new MainContentManager($env, $page); // todo cache it ?!

        $extractorFunction = 'get'.ucfirst($name);

        if (!method_exists($mainContentManager, $extractorFunction)) {
            throw new Exception('`'.$name.'` does not exist');
        }

        return $mainContentManager->$extractorFunction();
    }

    public static function getEmbedCode(
        $embed_code
    ) {
        if ($id = self::getYoutubeVideo($embed_code)) {
            $embed_code = '<iframe src=https://www.youtube-nocookie.com/embed/'.$id.' frameborder=0'
                    .' allow=autoplay;encrypted-media allowfullscreen class=w-100></iframe>';
        }

        return $embed_code;
    }

    public function renderVideo(
        Twig $env,
        $embed_code,
        $image,
        $alt,
        $btn_title = null,
        $btn_style = null
    ) {
        $embed_code = self::getEmbedCode($embed_code);

        $params = [
            'embed_code' => $embed_code,
            'image' => $image,
            'alt' => $alt,
        ];

        if (null !== $btn_title) {
            $params['btn_title'] = $btn_title;
        }
        if (null !== $btn_style) {
            $params['btn_style'] = $btn_style;
        }

        return $env->render('@PiedWebCMS/component/_video.html.twig', $params);
    }

    protected static function getYoutubeVideo($url)
    {
        if (preg_match('~^(?:https?://)?(?:www[.])?(?:youtube[.]com/watch[?]v=|youtu[.]be/)([^&]{11})~', $url, $m)) {
            return $m[1];
        }
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
            'mail_readable' => self::readableEncodedMail($mail),
            'mail_encoded' => str_rot13($mail),
            'mail' => $mail,
            'class' => $class,
        ]);
    }

    public static function readableEncodedMail($mail)
    {
        return str_replace('@', '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-at" '
        .'fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M13.106 '
        .'7.222c0-2.967-2.249-5.032-5.482-5.032-3.35 0-5.646 2.318-5.646 5.702 0 3.493 2.235 5.708 5.762'
        .' 5.708.862 0 1.689-.123 2.304-.335v-.862c-.43.199-1.354.328-2.29.328-2.926 0-4.813-1.88-4.813-4.798'
        .' 0-2.844 1.921-4.881 4.594-4.881 2.735 0 4.608 1.688 4.608 4.156 0 1.682-.554 2.769-1.416 2.769-.492'
        .' 0-.772-.28-.772-.76V5.206H8.923v.834h-.11c-.266-.595-.881-.964-1.6-.964-1.4 0-2.378 1.162-2.378 2.823 0'
        .' 1.737.957 2.906 2.379 2.906.8 0 1.415-.39 1.709-1.087h.11c.081.67.703 1.148 1.503 1.148 1.572 0 2.57-1.415'
        .' 2.57-3.643zm-7.177.704c0-1.197.54-1.907 1.456-1.907.93 0 1.524.738 1.524 1.907S8.308 9.84 7.371 9.84c-.895'
        .' 0-1.442-.725-1.442-1.914z"/></svg>', $mail);
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

    public static function encode($string)
    {
        return str_rot13($string);
    }
}
