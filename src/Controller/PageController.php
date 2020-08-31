<?php

namespace PiedWeb\CMSBundle\Controller;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Service\ConfigHelper as Helper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends AbstractController
{
    public function show(
        ?string $slug,
        Request $request,
        ParameterBagInterface $params
    ) {
        $slug = (null === $slug || '' === $slug) ? 'homepage' : rtrim(strtolower($slug), '/');
        $page = $this->getDoctrine()
            ->getRepository($params->get('pwc.entity_page'))
            ->getPage($slug, Helper::get($request, $params)->getHost(), Helper::get($request, $params)->isFirstApp());

        // Check if page exist
        if (null === $page) {
            throw $this->createNotFoundException();
        }

        if (null !== $page->getLocale()) { // avoid bc break, todo remove it
            $request->setLocale($page->getLocale());
            $this->get('translator')->setLocale($page->getLocale());
        }

        // Check if page is public
        if ($page->getCreatedAt() > new \DateTimeImmutable() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException();
        }

        // SEO redirection if we are not on the good URI (for exemple /fr/tagada instead of /tagada)
        $redirect = $this->checkIfUriIsCanonical($request, $page);
        if (false !== $redirect) {
            return $this->redirect($redirect[0], $redirect[1]);
        }

        // Maybe the page is a redirection
        if ($page->getRedirection()) {
            return $this->redirect($page->getRedirection(), $page->getRedirectionCode());
        }

        $template = Helper::get($request, $params)->getDefaultTemplate();

        return $this->render($template, [
            'page' => $page,
            'app_base_url' => Helper::get($request, $params)->getBaseUrl(),
            'app_name' => Helper::get($request, $params)->getApp('name'),
            'app_color' => Helper::get($request, $params)->getApp('color'),
        ]);
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
        Request $request,
        ParameterBagInterface $params
    ) {
        $pageEntity = $params->get('pwc.entity_page');

        $page = (null === $slug || '' === $slug) ?
            new $pageEntity()
            : $this->getDoctrine()
            ->getRepository($pageEntity)
            ->findOneBy(['slug' => rtrim(strtolower($slug), '/')]);

        $page->setMainContent($request->request->get('plaintext')); // todo update all fields to avoid errors

        return $this->render('@PiedWebCMS/admin/page_preview.html.twig', [
            'page' => $page,
            'app_base_url' => Helper::get($request, $params)->getBaseUrl(),
            'app_name' => Helper::get($request, $params)->getApp('name'),
            'app_color' => Helper::get($request, $params)->getApp('color'),
        ]);
    }

    public function feed(
        ?string $slug,
        Request $request,
        ParameterBagInterface $params
    ) {
        if ('homepage' == $slug) {
            return $this->redirect($this->generateUrl('piedweb_cms_page_feed', ['slug' => 'index']), 301);
        }

        $slug = (null === $slug || 'index' === $slug) ? 'homepage' : rtrim(strtolower($slug), '/');
        $page = $this->getDoctrine()
            ->getRepository($params->get('pwc.entity_page'))
            ->getPage($slug, Helper::get($request, $params)->getHost(), Helper::get($request, $params)->isFirstApp());

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

        return $this->render('@PiedWebCMS/page/rss.xml.twig', [
            'page' => $page,
            'app_base_url' => Helper::get($request, $params)->getBaseUrl(),
            'app_name' => Helper::get($request, $params)->getApp('name'),
        ], $response);
    }
}
