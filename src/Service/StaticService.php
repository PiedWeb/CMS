<?php

namespace PiedWeb\CMSBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\EventListener\MediaCacheGeneratorTrait;
use PiedWeb\CMSBundle\Service\PageCanonicalService as PageCanonical;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as Twig;
use \WyriHaximus\HtmlCompress\Factory as HtmlCompressor;

class StaticService
{
    //use MediaCacheGeneratorTrait;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var \Twig_environement
     */
    private $twig;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @var string
     */
    private $staticDir;

    /**
     * @var RequestStack
     */
    private $requesStack;

    /**
     * @var \PiedWeb\CMSBundle\Service\PageCanonicalService
     */
    private $pageCanonical;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $parser;

    private $params;

    protected $redirections = '';

    private $cacheManager;
    private $dataManager;
    private $filterManager;

    public function __construct(
        EntityManagerInterface $em,
        Twig $twig,
        ParameterBagInterface $params,
        RequestStack $requesStack,
        PageCanonical $pageCanonical,
        TranslatorInterface $translator,
        CacheManager $cacheManager,
        DataManager $dataManager,
        FilterManager $filterManager,
        string $webDir
    ) {
        $this->em = $em;
        $this->filesystem = new Filesystem();
        $this->twig = $twig;
        $this->params = $params;
        $this->requesStack = $requesStack;
        $this->webDir = $webDir;
        $this->pageCanonical = $pageCanonical;
        $this->translator = $translator;
        $this->cacheManager = $cacheManager;
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->staticDir = $this->webDir.'/../static';
        $this->parser = HtmlCompressor::construct();
    }

    /**
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function dump()
    {
        if (!method_exists($this->filesystem, 'dumpFile')) {
            throw new \RuntimeException('Method dumpFile() is not available. Upgrade your Filesystem.');
        }

        $this->rmdir($this->staticDir);
        $this->pageToStatic();
        $this->assetsToStatic();
        $this->htaccessToStatic();
        $this->mediaToDownload();
    }

    protected function htaccessToStatic()
    {
        if (!$this->params->has('app.static_domain')) {
            throw new \Exception('Before, you need to configure (in config/services.yaml) app.static_domain.');
        }

        $htaccess = $this->twig->render('@PiedWebCMS/static/htaccess.twig', [
            'domain' => $this->params->get('app.static_domain'),
            'redirections' => $this->redirections,
        ]);
        $this->filesystem->dumpFile($this->staticDir.'/.htaccess', $htaccess);
    }

    protected static function rmdir($dir)
    {
        if (is_link($dir)) {
            unlink($dir);
        }

        if (!is_dir($dir)) {
            return false;
        }

        $dir_handle = opendir($dir);
        if (!$dir_handle) {
            return false;
        }

        while ($file = readdir($dir_handle)) {
            if ('.' != $file && '..' != $file) {
                if (!is_dir($dir.'/'.$file)) {
                    unlink($dir.'/'.$file);
                } else {
                    self::rmdir($dir.'/'.$file);
                }
            }
        }
        closedir($dir_handle);
        rmdir($dir);

        return true;
    }

    protected function symlink(string $target, string $link): bool
    {
        // Check for symlinks
        if (is_link($target)) {
            return symlink(readlink($target), $link);
        }

        return symlink(
            $target,
            $link
        );
    }

    /**
     * We create symlink for all assets
     * and we copy feed.xml, sitemap.xml, robots.txt (avoid new page not yet generated try to be indexed by bots).
     */
    protected function assetsToStatic(): self
    {
        $dir = dir($this->webDir);
        while (false !== $entry = $dir->read()) {
            if ('.' == $entry || '..' == $entry) {
                continue;
            }
            if (in_array($entry, ['robots.txt', 'feed.xml', 'sitemap.xml', 'sitemap.txt'])) {
                copy(
                    str_replace($this->params->get('kernel.project_dir').'/', '../', $this->webDir.'/'.$entry),
                    $this->staticDir.'/'.$entry
                );
            } elseif ('index.php' != $entry) {
                $this->symlink(
                    str_replace($this->params->get('kernel.project_dir').'/', '../', $this->webDir.'/'.$entry),
                    $this->staticDir.'/'.$entry
                );
            }
        }
        $dir->close();

        return $this;
    }

    protected function mediaToDownload()
    {
        $this->filesystem->mkdir($this->staticDir.'/download/');
        symlink($this->webDir.'/../media', $this->staticDir.'/download/media');

        /* create download symlink *
        if (!file_exists($this->staticDir.'/download')) {
            mkdir($this->staticDir.'/download');
        }
        $this->symlink(
            str_replace($this->params->get('kernel.project_dir').'/', '../', $this->webDir.'/../media'),
            $this->staticDir.'/download/media'
        );
        /**/
    }

    protected function pageToStatic(): self
    {
        $pages = $this->getPages();

        // todo i18n error
        $locales = explode('|', $this->params->get('app.locales'));

        foreach ($locales as $locale) {
            $this->filesystem->mkdir($this->staticDir.'/'.$locale);
            $this->generateErrorPage($locale);
        }

        foreach ($pages as $page) {
            // set current locale to avoid twig error
            $request = new Request();
            $request->setLocale($page->getLocale());
            $this->requesStack->push($request);

            $page->setTranslatableLocale($page->getLocale());
            $this->em->refresh($page);

            $this->translator->setLocale($page->getLocale());

            $slug = '' == $page->getRealSlug() ? 'index' : $page->getRealSlug();
            $route = $this->pageCanonical->generatePathForPage($slug);
            $filepath = $this->staticDir.$route.'.html';

            // check if it's a redirection
            if (false !== $page->getRedirection()) {
                $this->redirections .= 'Redirect ';
                $this->redirections .= $page->getRedirectionCode().' '.$route.' '.$page->getRedirection();
                $this->redirections .= PHP_EOL;
                continue;
            }

            $dump = $this->render($page);
            $this->filesystem->dumpFile($filepath, $dump);

            if ($page->getChildrenPages()->count() > 0) {
                $dump = $this->renderFeed($page);
                $this->filesystem->dumpFile(preg_replace('/.html$/', '.xml', $filepath), $dump);
            }
        }

        return $this;
    }

    protected function generateErrorPage($locale = null)
    {
        if (null !== $locale) {
            $request = new Request();
            $request->setLocale($locale);
            $this->requesStack->push($request);
        }

        $dump = $this->parser->compress($this->twig->render('@Twig/Exception/error.html.twig'));
        $this->filesystem->dumpFile($this->staticDir.(null !== $locale ? '/'.$locale : '').'/_error.html', $dump);
    }

    protected function getPages()
    {
        $qb = $this->em->getRepository($this->params->get('app.entity_page'))->getQueryToFindPublished('p');

        return $qb->getQuery()->getResult();
    }

    protected function render(Page $page)
    {
        $template = $this->params->get('app.default_page_template');

        return $this->parser->compress($this->twig->render($template, ['page' => $page]));
    }

    // todo i18n feed ...
    protected function renderFeed(Page $page)
    {
        $template = '@PiedWebCMS/page/rss.xml.twig';

        return $this->parser->compress($this->twig->render($template, ['page' => $page]));
    }
}
