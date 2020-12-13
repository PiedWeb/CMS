<?php

namespace PiedWeb\CMSBundle\PageScanner;

use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Service\AppConfigHelper as App;
use PiedWeb\CMSBundle\Service\AppConfigHelper;
use PiedWeb\CMSBundle\Utils\GenerateLivePathForTrait;
use PiedWeb\CMSBundle\Utils\KernelTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as Twig_Environment;

/**
 * Permit to find error in image or link.
 */
class PageScannerService
{
    use KernelTrait, GenerateLivePathForTrait;

    /**
     * @var AppConfigHelper
     */
    protected $app;

    protected $em;
    protected $pageHtml;
    protected $twig;
    protected $currentPage;
    protected $webDir;
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

    public function scan(PageInterface $page)
    {
        $this->app = new AppConfigHelper($page->getHost(), $this->apps);
        $this->currentPage = $page;
        $this->errors = [];
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
        }
        elseif (200 != $response->getStatusCode()) {
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

    protected static function prepareForRegex ($var)
    {
        if (is_string($var)) {
            return preg_quote($var, '/');
        }

        $var = array_map('static::prepareForRegex', $var);

        return '('.implode('|', $var).')';
    }

    public static function isWebLink(string $url)
    {
        return preg_match('@^((?:(http:|https:)//([\w\d-]+\.)+[\w\d-]+){0,1}(/?[\w~,;\-\./?%&+#=]*))$@', $url);
    }

    protected function getLinkedDocs(): array
    {
        $urlInAttributes = ' '.self::prepareForRegex(['href', 'data-rot', 'src', 'data-img', 'data-bg']);
        $regex = '/'.$urlInAttributes.'=((["\'])([^\3]+)\3|([^\s>]+)[\s>])/iU';
        preg_match_all(            $regex,            $this->pageHtml,            $matches        );

        $linkedDocs = [];
        foreach ($matches[0] as $k => $match) {
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

    protected function removeBase($url) {
        if (strpos($url, 'https://'.$this->app->getMainHost()) === 0) {
            return substr($url, strlen('https://'.$this->app->getMainHost()));
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
            $this->linksCheckedCounter++;
            if (!is_string($uri))
                continue;
            if (($uri[0] == '/' && !$this->uriExist($uri) )
                || (strpos($uri, 'http') === 0 && !$this->urlExist($uri))) {
                $this->addError('<code>'.$uri.'</code> introuvable');
            }
        }
    }

    protected function urlExist($uri) {
        // todo check external resource
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
