<?php

namespace PiedWeb\CMSBundle\Twig;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Entity\Media;
use PiedWeb\CMSBundle\Entity\MediaExternal;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Extension\Router\RouterInterface;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\RenderAttributes\AttributesTrait;
use Twig\Environment as Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    use AttributesTrait;
    use EmailTwigTrait;
    use EncryptedLinkTwigTrait;
    use GalleryTwigTrait;
    use ListTwigTrait;
    use PhoneNumberTwigTrait;
    use TxtAnchorTwigTrait;
    use VideoTwigTrait;

    /** @var RouterInterface */
    private $router;

    /** @var EntityManagerInterface */
    private $em;

    /** @var string */
    private $pageClass;

    /** @var App */
    private $apps;

    /** @var Twig */
    private $twig;

    public function __construct(EntityManagerInterface $em, string $pageClass, RouterInterface $router, App $apps, Twig $twig)
    {
        $this->em = $em;
        $this->router = $router;
        $this->pageClass = $pageClass;
        $this->apps = $apps;
        $this->twig = $twig;
    }

    public function getApp()
    {
        return $this->apps->get();
    }

    public function getFilters()
    {
        return [
            new TwigFilter('html_entity_decode', 'html_entity_decode'),
            new TwigFilter('slugify', [(new Slugify()), 'slugify']),
            new TwigFilter('preg_replace', [self::class, 'pregReplace']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('template_from_string', 'addslashes'), // TODO FIX IT
            new TwigFunction('isCurrentPage', [$this, 'isCurrentPage']), // used ?
            new TwigFunction('isInternalImage', [self::class, 'isInternalImage']), // used ?
            new TwigFunction('getImageFrom', [self::class, 'transformInlineImageToMedia']), // used ?
            // loaded from trait
            new TwigFunction('jslink', [$this, 'renderEncryptedLink'], self::options()),
            new TwigFunction('link', [$this, 'renderEncryptedLink'], self::options()),
            new TwigFunction('mail', [$this, 'renderEncodedMail'], self::options(true)),
            new TwigFunction('email', [$this, 'renderEncodedMail'], self::options(true)),
            new TwigFunction('tel', [$this, 'renderPhoneNumber'], self::options()),
            new TwigFunction('bookmark', [$this, 'renderTxtAnchor'], self::options()),
            new TwigFunction('anchor', [$this, 'renderTxtAnchor'], self::options()),
            new TwigFunction('gallery', [$this, 'renderGallery'], self::options()),
            new TwigFunction('video', [$this, 'renderGallery'], self::options()),
            new TwigFunction('getEmbedCode', [self::class, 'getEmbedCode']),
            new TwigFunction('list', [$this, 'renderPagesList'], self::options()),
        ];
    }

    public static function options($needsEnv = false, $isSafe = ['html'])
    {
        return ['is_safe' => $isSafe, 'needs_environment' => $needsEnv];
    }

    public static function isInternalImage(string $media): bool
    {
        return 0 === strpos($media, '/media/default/');
    }

    public static function transformInlineImageToMedia(string $src)
    {
        return self::isInternalImage($src) ? Media::loadFromSrc($src) : MediaExternal::load($src);
    }

    public function isCurrentPage(string $uri, ?Page $currentPage)
    {
        return
            null === $currentPage || $uri != $this->router->generate($currentPage->getRealSlug())
            ? false
            : true;
    }

    public static function pregReplace($subject, $pattern, $replacement)
    {
        return preg_replace($pattern, $replacement, $subject);
    }
}
