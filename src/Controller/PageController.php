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

    public function show(string $slug = 'homepage', Request $request, TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $slug = '' == $slug ? 'homepage' : $slug;
        $page = $this->getDoctrine()->getRepository($this->container->getParameter('app.entity_page'))->findOneBySlug($slug, $request->getLocale());

        // Check if page exist
        if (null === $page) {
            throw $this->createNotFoundException();
        }

        // Check if page is public
        if ($page->getCreatedAt() > new \DateTimeImmutable() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException();
        }

        $redirect = $this->checkIfUriIsCanonical($request, $page);
        if (false !== $redirect) {
            return $this->redirect($redirect[0], $redirect[1]);
        }

        $redirect = $this->getRedirection($page);
        if (false !== $redirect) {
            return $this->redirect($redirect[0], $redirect[1]);
        }

        $template = method_exists($this->container->getParameter('app.entity_page'), 'getTemplate') && null !== $page->getTemplate() ? $page->getTemplate() : $params->get('app.default_page_template');

        return $this->render($template, ['page' => $page]);
    }

    protected function checkIfUriIsCanonical($request, $page)
    {
        $real = $request->getRequestUri();

        $defaultLocale = $this->container->getParameter('locale');

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

    /*
     * todo: paginate
     */
    public function showList(?Page $page, $render = '@PiedWebCMS/page/_list.html.twig', $limit = 100)
    {
        $qb = $this->getDoctrine()->getRepository($this->container->getParameter('app.entity_page'))->getQueryToFindPublished('p');
        if (null !== $page) {
            $qb->andWhere('p.parentPage = :id');
            $qb->setParameter('id', $page->getId());
        }
        $qb->orderBy('p.createdAt', 'DESC');
        $qb->setMaxResults($limit);

        $pages = $qb->getQuery()->getResult();

        return $this->render($render, ['pages' => $pages]);
    }

    /**
     * Check if a content don't start by 'Location: http://valid-url.tld/eg'.
     */
    protected function getRedirection($page)
    {
        $content = $page->getMainContent();
        $code = 301; // default symfony is 302...
        if ('Location:' == substr($content, 0, 9)) {
            $url = trim(substr($content, 9));
            if (preg_match('/ [1-5][0-9]{2}$/', $url, $match)) {
                $code = intval(trim($match[0]));
                $url = preg_replace('/ [1-5][0-9]{2}$/', '', $url);
            }
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return [$url, $code];
            }
        }

        return false;
    }
}
