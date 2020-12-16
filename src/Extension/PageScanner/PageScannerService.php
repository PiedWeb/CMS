<?php

namespace PiedWeb\CMSBundle\Extension\PageScanner;

use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\CMSBundle\Utils\GenerateLivePathForTrait;
use PiedWeb\CMSBundle\Utils\KernelTrait;
use PiedWeb\UrlHarvester\Harvest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as Twig_Environment;

/**
 * Permit to find error in image or link.
 */
class PageScannerService
{
    use GenerateLivePathForTrait;
    use KernelTrait;

    /**
     * @var App
     */
    protected $app;

    protected $em;
    protected $pageHtml;
    protected $twig;
    protected $currentPage;
    protected $webDir;
    protected $previousRequest;
    protected $apps;
    protected $linksCheckedCounter = 0;
    protected $errors = [];
    protected $everChecked = [];
    public static $appKernel;

    public function __construct(
        Twig_Environment $twig,
        EntityManagerInterface $em,
        string $webDir,
        array $apps,
        RouterInterface $router,
        KernelInterface $kernel
    ) {
        $this->twig = $twig;
        $this->router = $router;
        $this->em = $em;
        $this->webDir = $webDir;
        $this->apps = $apps;

        static::loadKernel($kernel);
    }

    protected function resetErrors()
    {
        $this->errors = [];
    }

    public function scan(PageInterface $page)
    {
        $this->app = new App($page->getHost(), $this->apps);
        $this->currentPage = $page;
        $this->resetErrors();
        $this->pageHtml = '';

        if (false !== $page->getRedirection()) {
            // check $page->getRedirection() return 20X

            return true; // or status code
        }

        $liveUri = $this->generateLivePathFor($page);
        $this->pageHtml = $this->getHtml($liveUri);

        // 2. Je récupère tout les liens et je les check
        // href="", data-rot="" data-img="", src="", data-bg
        if ($this->pageHtml) {
            $this->checkLinkedDocs($this->getLinkedDocs());
        }

        return empty($this->errors) ? true : $this->errors;
    }

    protected function getHtml($liveUri)
    {
        $request = Request::create($liveUri);
        $response = static::$appKernel->handle($request);

        if ($response->isRedirect()) {
            $linkedDocs[] = $response->headers->get('location');

            return;
        } elseif (200 != $response->getStatusCode()) {
            $this->addError('error on generating the page ('.$response->getStatusCode().')');
            exit;

            return;
        }

        return $response->getContent();
    }

    protected function addError($message)
    {
        $this->errors[] = [
            'message' => $message,
            'page' => $this->currentPage,
        ];
    }

    protected static function prepareForRegex($var)
    {
        if (\is_string($var)) {
            return preg_quote($var, '/');
        }

        $var = array_map('static::prepareForRegex', $var);

        return '('.implode('|', $var).')';
    }

    protected static function isWebLink(string $url)
    {
        return preg_match('@^((?:(http:|https:)//([\w\d-]+\.)+[\w\d-]+){0,1}(/?[\w~,;\-\./?%&+#=]*))$@', $url);
    }

    protected function getLinkedDocs(): array
    {
        $urlInAttributes = ' '.self::prepareForRegex(['href', 'data-rot', 'src', 'data-img', 'data-bg']);
        $regex = '/'.$urlInAttributes.'=((["\'])([^\3]+)\3|([^\s>]+)[\s>])/iU';
        preg_match_all($regex, $this->pageHtml, $matches);

        $linkedDocs = [];
        $matchesCount = \count($matches[0]);
        for ($k = 0; $k < $matchesCount; ++$k) {
            $uri = isset($matches[4][$k]) ? $matches[4][$k] : $matches[5][$k];
            $uri = 'data-rot' == $matches[1][$k] ? str_rot13($uri) : $uri;
            $uri = strtok($uri, '#');
            $uri = $this->removeBase($uri);
            if ('' !== $uri && self::isWebLink($uri)) {
                $linkedDocs[] = $uri;
            }
        }

        return array_unique($linkedDocs);
    }

    protected function removeBase($url)
    {
        if (0 === strpos($url, 'https://'.$this->app->getMainHost())) {
            return substr($url, \strlen('https://'.$this->app->getMainHost()));
        }

        return $url;
    }

    public function getLinksCheckedCounter()
    {
        return $this->linksCheckedCounter;
    }

    protected function checkLinkedDocs(array $linkedDocs)
    {
        foreach ($linkedDocs as $uri) {
            ++$this->linksCheckedCounter;
            if (!\is_string($uri)) {
                continue;
            }
            if (('/' == $uri[0] && !$this->uriExist($uri))
                || (0 === strpos($uri, 'http') && !$this->urlExist($uri))) {
                $this->addError('<code>'.$uri.'</code> introuvable');
            }
        }
    }

    /**
     * this is really slow on big website.
     *
     * @param string $uri
     *
     * @return bool
     */
    protected function urlExist($uri)
    {
        $harvest = Harvest::fromUrl(
            $uri,
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.107 Safari/537.36',
            'en,en-US;q=0.5',
            $this->previousRequest
        );

        if (\is_int($harvest) || 200 !== $harvest->getResponse()->getStatusCode()) {
            return false;
        }

        $this->previousRequest = $harvest->getResponse()->getRequest();

        return true;
    }

    protected function uriExist($uri)
    {
        $slug = ltrim($uri, '/');

        if (isset($this->everChecked[$slug])) {
            return $this->everChecked[$slug];
        }

        $checkDatabase = 0 !== strpos($slug, 'media/'); // we avoid to check in db the media, file exists is enough
        $page = true !== $checkDatabase ? null : $this->em->getRepository(\get_class($this->currentPage))
            ->findOneBy(['slug' => '' == $slug ? 'homepage' : $slug]); // todo add domain check (currentPage domain)

        $this->everChecked[$slug] = (
            null === $page
                && !file_exists($this->webDir.'/'.$slug)
                && 'feed.xml' !== $slug
        ) ? false : true;

        return $this->everChecked[$slug];
    }
}
