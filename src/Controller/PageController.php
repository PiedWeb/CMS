<?php

namespace PiedWeb\CMSBundle\Controller;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Service\AppConfigHelper as App;
use PiedWeb\CMSBundle\Service\Repository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends AbstractController
{
    protected $params;

    public function __construct(
        ParameterBagInterface $params
    ) {
        $this->params = $params;
    }

    public function show(
        ?string $slug,
        ?string $host,
        Request $request
    ) {
        $app = App::load($host ?? $request, $this->params);
        $slug = (null === $slug || '' === $slug) ? 'homepage' : rtrim(strtolower($slug), '/');
        $page = Repository::getPageRepository($this->getDoctrine(), $this->params->get('pwc.entity_page'))
            ->getPage($slug, $host ?? $app->getHost(), $app->isFirstApp());

        // Check if page exist
        if (null === $page) {
            throw $this->createNotFoundException();
        }

        if (!$page->getLocale()) { // avoid bc break
            $page->setLocale($this->params->get('pwc.locale'));
        }

        $request->setLocale($page->getLocale());
        $this->get('translator')->setLocale($page->getLocale());

        // Check if page is public
        if ($page->getCreatedAt() > new \DateTimeImmutable() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException();
        }

        // SEO redirection if we are not on the good URI (for exemple /fr/tagada instead of /tagada)
        if ((null === $host || $host == $request->getHost())
            && false !== $redirect = $this->checkIfUriIsCanonical($request, $page)) {
            return $this->redirect($redirect[0], $redirect[1]);
        }

        // Maybe the page is a redirection
        if ($page->getRedirection()) {
            return $this->redirect($page->getRedirection(), $page->getRedirectionCode());
        }

        $template = $app->getDefaultTemplate();

        return $this->render($template, array_merge(['page' => $page], $app->getParamsForRendering()));
    }

    protected function checkIfUriIsCanonical(Request $request, Page $page)
    {
        $real = $request->getRequestUri();

        $expected = 'homepage' == $page->getSlug() ?
            $this->get('piedweb.page_canonical')->generatePathForHomepage() :
            $this->get('piedweb.page_canonical')->generatePathForPage($page->getRealSlug());

        if ($real != $expected) {
            return [$request->getBasePath().$expected, 301];
        }

        return false;
    }

    public function preview(
        ?string $slug,
        Request $request
    ) {
        $app = App::load($request, $this->params);
        $pageEntity = $this->params->get('pwc.entity_page');

        $page = (null === $slug || '' === $slug) ?
            new $pageEntity()
            : $this->getDoctrine()
            ->getRepository($pageEntity)
            ->findOneBy(['slug' => rtrim(strtolower($slug), '/')]);

        $page->setMainContent($request->request->get('plaintext')); // todo update all fields to avoid errors

        return $this->render(
            '@PiedWebCMS/page/preview.html.twig',
            array_merge(['page' => $page], $app->getParamsForRendering())
        );
    }

    public function showFeed(
        ?string $slug,
        ?string $host,
        Request $request
    ) {
        if ('homepage' == $slug) {
            return $this->redirect($this->generateUrl('piedweb_cms_page_feed', ['slug' => 'index']), 301);
        }

        $app = App::load($host ?? $request, $this->params);
        $slug = (null === $slug || 'index' === $slug) ? 'homepage' : rtrim(strtolower($slug), '/');
        $page = Repository::getPageRepository($this->getDoctrine(), $this->params->get('pwc.entity_page'))
            ->getPage($slug, $app->getHost(), $app->isFirstApp());

        // Check if page exist
        if (null === $page || $page->getChildrenPages()->count() < 1) {
            throw $this->createNotFoundException();
        }

        // Check if page is public
        if ($page->getCreatedAt() > new \DateTimeImmutable() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException();
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');

        return $this->render(
            '@PiedWebCMS/page/rss.xml.twig',
            array_merge(['page' => $page], $app->getParamsForRendering()),
            $response
        );
    }

    /**
     * Show Last created page in an XML Feed.
     */
    public function showMainFeed(
        ?string $host,
        Request $request
    ) {
        $app = App::load($host ?? $request, $this->params);
        // Retrieve info from homepage, for i18n, assuming it's named with locale
        $locale = $request->getLocale() ? rtrim($request->getLocale(), '/') : $this->params->get('locale');
        $LocaleHomepage = Repository::getPageRepository($this->getDoctrine(), $this->params->get('pwc.entity_page'))
            ->getPage($locale, $app->getHost(), $app->isFirstApp());
        $page = $LocaleHomepage ?? Repository::getPageRepository(
            $this->getDoctrine(),
            $this->params->get('pwc.entity_page')
        )
            ->getPage('homepage', $app->getHost(), $app->isFirstApp());

        if (!$page) {
            throw $this->createNotFoundException();
        }

        $params = [
            'pages' => $this->getPages(5, $request),
            'page' => $page,
            'feedUri' => ($this->params->get('locale') == $locale ? '' : $locale.'/').'feed.xml',
        ];

        return $this->render('@PiedWebCMS/page/rss.xml.twig', array_merge($params, $app->getParamsForRendering()));
    }

    public function showSitemap(
        ?string $host,
        Request $request
    ) {
        $app = App::load($host ?? $request, $this->params);
        $pages = $this->getPages(null, $request);

        if (!$pages) {
            throw $this->createNotFoundException();
        }

        return $this->render('@PiedWebCMS/page/sitemap.'.$request->getRequestFormat().'.twig', [
            'pages' => $pages,
            'app_base_url' => $app->getBaseUrl(),
        ]);
    }

    protected function getPages(?int $limit = null, Request $request)
    {
        $requestedLocale = rtrim($request->getLocale(), '/');

        $app = App::load($request, $this->params);
        $pages = Repository::getPageRepository($this->getDoctrine(), $this->params->get('pwc.entity_page'))
            ->getIndexablePages(
                $app->getHost(),
                $app->isFirstApp(),
                $requestedLocale,
                $this->params->get('locale'),
                $limit
            )->getQuery()->getResult();

        //foreach ($pages as $page) echo $page->getMetaRobots().' '.$page->getTitle().'<br>';
        //exit('feed updated');

        return $pages;
    }
}
