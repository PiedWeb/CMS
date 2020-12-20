<?php

namespace PiedWeb\CMSBundle\Controller;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Repository\Repository;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\CMSBundle\Service\AppConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class PageController extends AbstractController
{
    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**     * @var App     */
    protected $apps;

    /** @var AppConfig */
    protected $app;

    protected $twig;

    public function __construct(
        ParameterBagInterface $params,
        Twig $twig,
        App $app
    ) {
        $this->params = $params;
        $this->twig = $twig;
        $this->apps = $app;
    }

    public function show(?string $slug, ?string $host, Request $request): Response
    {
        $page = $this->getPage($slug, $host, $request);

        // SEO redirection if we are not on the good URI (for exemple /fr/tagada instead of /tagada)
        if ((null === $host || $host == $request->getHost())
            && false !== $redirect = $this->checkIfUriIsCanonical($request, $page)) {
            return $this->redirect($redirect[0], $redirect[1]);
        }

        // Maybe the page is a redirection
        if ($page->getRedirection()) {
            return $this->redirect($page->getRedirection(), $page->getRedirectionCode());
        }

        $params = array_merge(['page' => $page], $this->app->getParamsForRendering());
        return $this->render($this->getTemplate($page->getTemplate() ?: '/page/page.html.twig'), $params);

    }

    protected function getTemplate($path)
    {
        return $this->app->getView($path, $this->twig);
    }

    public function showFeed(?string $slug, ?string $host, Request $request)
    {
        $page = $this->getPage($slug, $host, $request);

        if ('homepage' == $slug) {
            return $this->redirect($this->generateUrl('piedweb_cms_page_feed', ['slug' => 'index']), 301);
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');

        return $this->render(
            $this->getTemplate('/page/rss.xml.twig'),
            array_merge(['page' => $page], $this->app->getParamsForRendering()),
            $response
        );
    }

    /**
     * Show Last created page in an XML Feed.
     */
    public function showMainFeed(?string $host, Request $request)
    {
        $this->setApp($host);
        $locale = $request->getLocale() ? rtrim($request->getLocale(), '/') : $this->app->getDefaultLocale();
        $LocaleHomepage = $this->getPage($locale, $host, $request, false);
        $slug = 'homepage';
        $page = $LocaleHomepage ?: $this->getPage($slug, $host, $request);

        $params = [
            'pages' => $this->getPages(5, $request),
            'page' => $page,
            'feedUri' => ($this->params->get('locale') == $locale ? '' : $locale.'/').'feed.xml',
        ];

        return $this->render(
            $this->getTemplate('/page/rss.xml.twig'),
            array_merge($params, $this->app->getParamsForRendering())
        );
    }

    public function showSitemap($_format, ?string $host, Request $request)
    {
        $this->setApp($host);
        $pages = $this->getPages(null, $request);

        if (! $pages) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            $this->getTemplate('/page/sitemap.'.$_format.'.twig'),
            [
                'pages' => $pages,
                'app_base_url' => $this->app->getBaseUrl(),
            ]
        );
    }

    protected function getPages(?int $limit = null, Request $request)
    {
        $requestedLocale = rtrim($request->getLocale(), '/');

        $pages = $this->getPageRepository()->getIndexablePages(
            $this->apps->getMainHost(),
            $this->apps->isFirstApp(),
            $requestedLocale,
            $this->params->get('locale'),
            $limit
        )->getQuery()->getResult();

        return $pages;
    }

    /**
     * @return \PiedWeb\CMSBundle\Repository\PageRepository
     */
    protected function getPageRepository()
    {
        return Repository::getPageRepository($this->getDoctrine(), $this->params->get('pwc.entity_page'));
    }

    protected function setApp($host): void
    {
        $this->app = $this->apps->switchCurrentApp($host)->get();
    }

    protected function getPage(?string &$slug, ?string $host = null, ?Request $request, $throwException = true): ?Page
    {
        $this->setApp($host); // TODO Move it on request listener (could be real host or parameter host)

        $slug = $this->noramlizeSlug($slug);
        $page = $this->getPageRepository()->getPage($slug, $this->app->getMainHost(), $this->app->isFirstApp());

        // Check if page exist
        if (null === $page) {
            if ($throwException) {
                throw $this->createNotFoundException();
            } else {
                return null;
            }
        }

        if (! $page->getLocale()) { // avoid bc break
            $page->setLocale($this->app->getDefaultLocale());
        }

        //if (null !== $request) { $request->setLocale($page->getLocale()); }
        $this->get('translator')->setLocale($page->getLocale());

        // Check if page is public
        if ($page->getCreatedAt() > new \DateTimeImmutable() && ! $this->isGranted('ROLE_EDITOR')) {
            if ($throwException) {
                throw $this->createNotFoundException();
            } else {
                return null;
            }
        }

        return $page;
    }

    protected function noramlizeSlug($slug)
    {
        return (null === $slug || '' === $slug) ? 'homepage' : rtrim(strtolower($slug), '/');
    }

    protected function checkIfUriIsCanonical(Request $request, Page $page)
    {
        $real = $request->getRequestUri();

        $expected = $this->generateUrl('piedweb_cms_page', ['slug' => $page->getRealSlug()]);

        if ($real != $expected) {
            return [$request->getBasePath().$expected, 301];
        }

        return false;
    }
}
