<?php

namespace PiedWeb\CMSBundle\Controller;

use PiedWeb\CMSBundle\Service\ConfigHelper as Helper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class SitemapController extends AbstractController
{
    public function showFeed(
        Request $request,
        ParameterBagInterface $params
    ) {
        // Retrieve info from homepage, for i18n, assuming it's named with locale
        $locale = $request->getLocale() ?? $params->get('locale');
        $LocaleHomepage = $this->getDoctrine()
            ->getRepository($params->get('pwc.entity_page'))
            ->getPage($locale, Helper::get($request, $params)->getHost(), Helper::get($request, $params)->isFirstApp());
        $page = $LocaleHomepage ?? $this->getDoctrine()
            ->getRepository($params->get('pwc.entity_page'))
            ->getPage('homepage', Helper::get($request, $params)->getHost(), Helper::get($request, $params)->isFirstApp());

        return $this->render('@PiedWebCMS/page/rss.xml.twig', [
            'pages' => $this->getPages(5, $request, $params),
            'page' => $page,
            'feedUri' => 'feed'.($params->get('locale') == $locale ? '' : '.'.$locale).'.xml',
            'app_base_url' => Helper::get($request, $params)->getBaseUrl(),
            'app_name' => Helper::get($request, $params)->getApp('name'),
        ]);
    }

    public function showSitemap(
        Request $request,
        ParameterBagInterface $params
    ) {
        $pages = $this->getPages(null, $request, $params);

        if (!$pages) {
            throw $this->createNotFoundException();
        }

        return $this->render('@PiedWebCMS/page/sitemap.'.$request->getRequestFormat().'.twig', [
            'pages' => $pages,
            'app_base_url' => Helper::get($request, $params)->getBaseUrl(),
        ]);
    }

    protected function getPages(?int $limit = null, Request $request, ParameterBagInterface $params)
    {
        $pages = $this->getDoctrine()->getRepository($params->get('pwc.entity_page'))->getIndexablePages(
            Helper::get($request, $params)->getHost(),
            Helper::get($request, $params)->isFirstApp(),
            $request->getLocale(),
            $params->get('locale'),
            $limit
        )->getQuery()->getResult();

        //foreach ($pages as $page) echo $page->getMetaRobots().' '.$page->getTitle().'<br>';
        //exit('feed updated');

        return $pages;
    }
}
