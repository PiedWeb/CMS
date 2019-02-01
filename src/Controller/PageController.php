<?php

namespace PiedWeb\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PageController extends AbstractController
{
    protected $translator;

    public function show(
        ?string $slug,
        Request $request,
        TranslatorInterface $translator,
        ParameterBagInterface $params
    ) {
        $slug = null === $slug ? 'homepage' : rtrim(strtolower($slug), '/');
        $page = $this->getDoctrine()
            ->getRepository($this->container->getParameter('app.entity_page'))
            ->findOneBySlug($slug, $request->getLocale());

        // Check if page exist
        if (null === $page) {
            throw $this->createNotFoundException();
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
        if (false !== $page->getRedirection()) {
            return $this->redirect($page->getRedirection(), $page->getRedirectionCode());
        }

        // method_exists($this->container->getParameter('app.entity_page'), 'getTemplate') &&
        $template = null !== $page->getTemplate() ? $page->getTemplate() : $params->get('app.default_page_template');

        return $this->render($template, ['page' => $page]);
    }

    protected function checkIfUriIsCanonical($request, $page)
    {
        $real = $request->getRequestUri();

        $expected = 'homepage' == $page->getSlug() ?
            $this->get('piedweb.page_canonical')->generatePathForHomepage() :
            $this->get('piedweb.page_canonical')->generatePathForPage($page->getRealSlug())
        ;

        if ($real != $expected) {
            return [$request->getBasePath().$expected, 301];
            // may log ?
        }

        return false;
    }
}
