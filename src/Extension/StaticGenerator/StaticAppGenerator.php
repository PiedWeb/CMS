<?php

namespace PiedWeb\CMSBundle\Extension\StaticGenerator;

use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Repository\PageRepository;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\CMSBundle\Utils\GenerateLivePathForTrait;
use PiedWeb\CMSBundle\Utils\KernelTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as Twig;
use WyriHaximus\HtmlCompress\Factory as HtmlCompressor;
use WyriHaximus\HtmlCompress\HtmlCompressorInterface;

/**
 * Generate 1 App.
 */
class StaticAppGenerator
{
    use GenerateLivePathForTrait;
    use KernelTrait;

    /**
     * Contain files relative to SEO wich will be hard copied.
     *
     * @var array
     */
    protected $robotsFiles = ['robots.txt'];

    /**
     * @var array
     */
    protected $dontCopy = ['index.php', '.htaccess'];

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Twig
     */
    protected $twig;

    /**
     * @var string
     */
    protected $webDir;

    /**
     * @var array
     */
    protected $apps;
    protected $app;
    protected $staticDomain;
    protected $mustGetPagesWithoutHost = true;

    /** var @string */
    protected $staticDir;

    /**
     * @var RequestStack
     */
    protected $requesStack;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var HtmlCompressorInterface
     */
    protected $parser;

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Used in .htaccess generation.
     *
     * @var string
     */
    protected $redirections = '';

    public function __construct(
        EntityManagerInterface $em,
        Twig $twig,
        ParameterBagInterface $params,
        RequestStack $requesStack,
        TranslatorInterface $translator,
        RouterInterface $router,
        string $webDir,
        KernelInterface $kernel
    ) {
        $this->em = $em;
        $this->filesystem = new Filesystem();
        $this->twig = $twig;
        $this->params = $params;
        $this->requesStack = $requesStack;
        $this->webDir = $webDir;
        $this->translator = $translator;
        $this->router = $router;
        $this->apps = $this->params->get('pwc.apps');
        $this->parser = HtmlCompressor::construct();

        if (! method_exists($this->filesystem, 'dumpFile')) {
            throw new \RuntimeException('Method dumpFile() is not available. Upgrade your Filesystem.');
        }

        static::loadKernel($kernel);
        $this->kernel = $kernel;
    }

    public function generateAll($filter = null)
    {
        foreach ($this->apps as $app) {
            if ($filter && ! \in_array($filter, $app->getHost())) {
                continue;
            }
            $this->generate($app, $this->mustGetPagesWithoutHost);
            //$this->generateStaticApp($app);

            $this->mustGetPagesWithoutHost = false;
        }

        return true;
    }

    public function generateFromHost($host)
    {
        return $this->generateAll($host);
    }

    /**
     * Main Logic is here.
     *
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function generate($app, $mustGetPagesWithoutHost = false)
    {
        $this->app = new App($app['hosts'][0], [$app]);
        $this->mustGetPagesWithoutHost = $mustGetPagesWithoutHost;

        $this->filesystem->remove($this->app->getStaticDir());
        $this->generatePages();
        $this->generateSitemaps();
        $this->generateErrorPages();
        $this->copyRobotsFiles();
        $this->generateServerManagerFile();
        $this->copyAssets();
        $this->copyMediaToDownload();
    }

    /**
     * Symlink doesn't work on github page, symlink only for apache if conf say OK to symlink.
     */
    protected function mustSymlink()
    {
        return $this->app->get('static_generateForApache') ? $this->app->get('static_symlinkMedia') : false;
    }

    /**
     * Generate .htaccess for Apache or CNAME for github
     * Must be run after generatePages() !!
     */
    protected function generateServerManagerFile()
    {
        if ($this->app->get('static_generateForApache')) {
            $this->generateHtaccess();
        } else { //if ($this->app['static_generateForGithubPages'])) {
            $this->generateCname();
        }
    }

    /**
     * Copy files relative to SEO (robots, sitemaps, etc.).
     */
    protected function copyRobotsFiles(): void
    {
        array_map([$this, 'copy'], $this->robotsFiles);
    }

    // todo
    // docs
    // https://help.github.com/en/github/working-with-github-pages/managing-a-custom-domain-for-your-github-pages-site
    protected function generateCname()
    {
        $this->filesystem->dumpFile($this->app->getStaticDir().'/CNAME', $this->app->getMainHost());
    }

    protected function generateHtaccess()
    {
        $htaccess = $this->twig->render('@PiedWebCMS/static/htaccess.twig', [
            'domain' => $this->app->getMainHost(),
            'redirections' => $this->redirections,
        ]);
        $this->filesystem->dumpFile($this->app->getStaticDir().'/.htaccess', $htaccess);
    }

    protected function copy(string $file): void
    {
        if (file_exists($file)) {
            copy(
                str_replace($this->params->get('kernel.project_dir').'/', '../', $this->webDir.'/'.$file),
                $this->app->getStaticDir().'/'.$file
            );
        }
    }

    /**
     * Copy (or symlink) for all assets in public
     * (and media previously generated by liip in public).
     */
    protected function copyAssets(): void
    {
        $symlink = $this->mustSymlink();

        $dir = dir($this->webDir);
        while (false !== $entry = $dir->read()) {
            if ('.' == $entry || '..' == $entry) {
                continue;
            }
            if (! \in_array($entry, $this->robotsFiles) && ! \in_array($entry, $this->dontCopy)) {
                //$this->symlink(
                if (true === $symlink) {
                    $this->filesystem->symlink(
                        str_replace($this->params->get('kernel.project_dir').'/', '../', $this->webDir.'/'.$entry),
                        $this->app->getStaticDir().'/'.$entry
                    );
                } else {
                    $action = is_file($this->webDir.'/'.$entry) ? 'copy' : 'mirror';
                    $this->filesystem->$action($this->webDir.'/'.$entry, $this->app->getStaticDir().'/'.$entry);
                }
            }
        }
        $dir->close();
    }

    /**
     * Copy or Symlink "not image" media to download folder.
     *
     * @return void
     */
    protected function copyMediaToDownload()
    {
        $symlink = $this->mustSymlink();

        if (! file_exists($this->app->getStaticDir().'/download')) {
            $this->filesystem->mkdir($this->app->getStaticDir().'/download/');
            $this->filesystem->mkdir($this->app->getStaticDir().'/download/media');
        }

        $dir = dir($this->webDir.'/../media');
        while (false !== $entry = $dir->read()) {
            if ('.' == $entry || '..' == $entry) {
                continue;
            }
            // if the file is an image, it's ever exist (maybe it's slow to check every files)
            if (! file_exists($this->webDir.'/media/default/'.$entry)) {
                if (true === $symlink) {
                    $this->filesystem->symlink(
                        '../../../media/'.$entry,
                        $this->app->getStaticDir().'/download/media/'.$entry
                    );
                } else {
                    $this->filesystem->copy(
                        $this->webDir.'/../media/'.$entry,
                        $this->app->getStaticDir().'/download/media/'.$entry
                    );
                }
            }
        }

        //$this->filesystem->$action($this->webDir.'/../media', $this->app->getStaticDir().'/download/media');
    }

    protected function generateSitemaps(): void
    {
        foreach (explode('|', $this->params->get('pwc.locales')) as $locale) {
            foreach (['txt', 'xml'] as $format) {
                $this->generateSitemap($locale, $format);
            }

            $this->generateFeed($locale);
        }
    }

    protected function generateSitemap($locale, $format)
    {
        $liveUri = $this->generateLivePathFor(
            $this->app->getMainHost(),
            'piedweb_cms_page_sitemap',
            ['locale' => $locale, '_format' => $format]
        );
        $staticFile = $this->app->getStaticDir().'/sitemap'.$locale.'.'.$format; // todo get it from URI removing host
        $this->saveAsStatic($liveUri, $staticFile);

        if ($this->params->get('locale') == $locale ? '' : '.'.$locale) {
            $staticFile = $this->app->getStaticDir().'/sitemap.'.$format;
            $this->saveAsStatic($liveUri, $staticFile);
        }
    }

    protected function generateFeed($locale)
    {
        $liveUri = $this->generateLivePathFor(
            $this->app->getMainHost(),
            'piedweb_cms_page_main_feed',
            ['locale' => $locale]
        );
        $staticFile = $this->app->getStaticDir().'/feed'.$locale.'.xml';
        $this->saveAsStatic($liveUri, $staticFile);

        if ($this->params->get('locale') == $locale ? '' : '.'.$locale) {
            $staticFile = $this->app->getStaticDir().'/feed.xml';
            $this->saveAsStatic($liveUri, $staticFile);
        }
    }

    /**
     * The function cache redirection found during generatePages and
     * format in self::$redirection the content for the .htaccess.
     *
     * @return void
     */
    protected function addRedirection(Page $page)
    {
        $this->redirections .= 'Redirect ';
        $this->redirections .= $page->getRedirectionCode().' ';
        $this->redirections .= $this->router->generate('piedweb_cms_page', ['slug' => $page->getRealSlug()]);
        $this->redirections .= ' '.$page->getRedirection();
        $this->redirections .= PHP_EOL;
    }

    protected function generatePages(): void
    {
        $qb = $this->getPageRepository()->getQueryToFindPublished('p');
        $qb = $this->getPageRepository()->andHost($qb, $this->app->getMainHost(), $this->mustGetPagesWithoutHost);
        $pages = $qb->getQuery()->getResult();

        foreach ($pages as $page) {
            $this->generatePage($page);
            //if ($page->getRealSlug()) $this->generateFeedFor($page);
        }
    }

    protected function generatePage(Page $page)
    {
        // check if it's a redirection
        if (false !== $page->getRedirection()) {
            $this->addRedirection($page);

            return;
        }

        $this->saveAsStatic($this->generateLivePathFor($page), $this->generateFilePath($page));
    }

    protected function saveAsStatic($liveUri, $destination)
    {
        $request = Request::create($liveUri);

        $response = static::$appKernel->handle($request);

        if ($response->isRedirect()) {
            // todo
            //$this->addRedirection($liveUri, getRedirectUri)
            return;
        } elseif (200 != $response->getStatusCode()) {
            //$this->kernel = static::$appKernel;
            if (500 === $response->getStatusCode() && 'dev' == $this->kernel->getEnvironment()) {
                exit($this->kernel->handle($request));
            }

            return;
        }

        $content = $this->compress($response->getContent());
        $this->filesystem->dumpFile($destination, $content);
    }

    protected function compress($html)
    {
        return $this->parser->compress($html);
    }

    protected function generateFilePath(Page $page)
    {
        $slug = '' == $page->getRealSlug() ? 'index' : $page->getRealSlug();
        $route = $this->router->generate('piedweb_cms_page', ['slug' => $page->getRealSlug()]);

        return $this->app->getStaticDir().$route.'.html';
    }

    /**
     * Generate static file for feed indexing children pages
     * (only if children pages exists).
     *
     * @return void
     */
    protected function generateFeedFor(Page $page)
    {
        $liveUri = $this->generateLivePathFor($page, 'piedweb_cms_page_feed');
        $staticFile = preg_replace('/.html$/', '.xml', $this->generateFilePath($page));
        $this->saveAsStatic($liveUri, $staticFile);
    }

    protected function generateErrorPages(): void
    {
        $this->generateErrorPage();

        // todo i18n error in .htaccess
        $locales = explode('|', $this->params->get('pwc.locales'));

        foreach ($locales as $locale) {
            $this->filesystem->mkdir($this->app->getStaticDir().'/'.$locale);
            $this->generateErrorPage($locale);
        }
    }

    protected function generateErrorPage($locale = null, $uri = '404.html')
    {
        if (null !== $locale) {
            $request = new Request();
            $request->setLocale($locale);
            $this->requesStack->push($request);
        }

        $dump = $this->parser->compress($this->twig->render('@Twig/Exception/error.html.twig'));
        $this->filesystem->dumpFile($this->app->getStaticDir().(null !== $locale ? '/'.$locale : '').'/'.$uri, $dump);
    }

    protected function getPageRepository(): PageRepository
    {
        return $this->em->getRepository($this->params->get('pwc.entity_page'));
    }
}
