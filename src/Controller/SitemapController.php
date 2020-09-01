<?php

namespace PiedWeb\CMSBundle\Controller;

use PiedWeb\CMSBundle\Service\AppConfigHelper as App;
use PiedWeb\CMSBundle\Service\Repository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class SitemapController extends AbstractController
{
    public function showFeed(
        Request $request,
        ParameterBagInterface $params
    ) {
        $app = App::get($request, $params);
        // Retrieve info from homepage, for i18n, assuming it's named with locale
        $locale = $request->getLocale() ?? $params->get('locale');
        $LocaleHomepage = Repository::getPageRepository($this->getDoctrine(), $params->get('pwc.entity_page'))
            ->getPage($locale, $app->getHost(), $app->isFirstApp());
        $page = $LocaleHomepage ?? Repository::getPageRepository($this->getDoctrine(), $params->get('pwc.entity_page'))
            ->getPage('homepage', $app->getHost(), $app->isFirstApp());

        return $this->render('@PiedWebCMS/page/rss.xml.twig', [
            'pages' => $this->getPages(5, $request, $params),
            'page' => $page,
            'feedUri' => 'feed'.($params->get('locale') == $locale ? '' : '.'.$locale).'.xml',
            'app_base_url' => $app->getBaseUrl(),
            'app_name' => $app->getApp('name'),
        ]);
    }

    public function showSitemap(
        Request $request,
        ParameterBagInterface $params
    ) {
        $app = App::get($request, $params);
        $pages = $this->getPages(null, $request, $params);

        if (!$pages) {
            throw $this->createNotFoundException();
        }

        return $this->render('@PiedWebCMS/page/sitemap.'.$request->getRequestFormat().'.twig', [
            'pages' => $pages,
            'app_base_url' => $app->getBaseUrl(),
        ]);
    }

    protected function getPages(?int $limit = null, Request $request, ParameterBagInterface $params)
    {
        $app = App::get($request, $params);
        $pages = Repository::getPageRepository($this->getDoctrine(), $params->get('pwc.entity_page'))
            ->getIndexablePages(
                $app->getHost(),
                $app->isFirstApp(),
                $request->getLocale(),
                $params->get('locale'),
                $limit
            )->getQuery()->getResult();

        //foreach ($pages as $page) echo $page->getMetaRobots().' '.$page->getTitle().'<br>';
        //exit('feed updated');

        return $pages;
    }
}
