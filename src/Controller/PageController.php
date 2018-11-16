<?php

namespace PiedWeb\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use PiedWeb\CMSBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class PageController extends AbstractController
{
    protected $translator;

    public function show(string $slug = 'homepage', Request $request, TranslatorInterface $translator)
    {
        $slug = '' == $slug ? 'homepage' : $slug;
        $page = $this->getDoctrine()->getRepository(Page::class)->findOneBySlug($slug, $request->getLocale());

        // Check if page exist
        if (null === $page && 'homepage' == $slug) {
            $page = new Page();
            $page->setTitle($translator->trans('installation.new.title'))
                 ->setExcrept($translator->trans('installation.new.text'));

            return $this->render('@PiedWebCMS/page/page.html.twig', ['page' => $page]);
        } elseif (null === $page) {
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

        $template = method_exists(Page::class, 'getTemplate') && null !== $page->getTemplate() ? $page->getTemplate() : '@PiedWebCMS/page/page.html.twig';
        // transfer '@PiedWebCMS/page/page.html.twig' to config file :)

        return $this->render($template, ['page' => $page]);
    }

    protected function checkIfUriIsCanonical($request, $page)
    {
        $real = $request->getRequestUri();

        $defaultLocale = $this->container->getParameter('locale');

        $expected = 'homepage' == $page->getSlug() && $defaultLocale == $request->getLocale() ?
            // preg_replace to keep homepage on mydomain.tld more than mydomain.tld/default-locale/
            //preg_replace('/'.$defaultLocale.'$/', '', $this->get('router')->generate('piedweb_cms_page')) :
            $this->get('router')->generate('piedweb_cms_homepage') :
            $this->get('router')->generate('piedweb_cms_page', ['slug' => $page->getRealSlug()])
        ;

        /**
        echo '<pre>';
        var_dump($this->get('router')->generate('piedweb_cms_homepage'));
        var_dump(rtrim($this->get('router')->generate('piedweb_cms_homepage'), $defaultLocale.'/'));
        var_dump($defaultLocale);
        var_dump($expected);
        var_dump($real);
        var_dump($real != $expected);
        echo '</pre>';
         **/
        //return false;
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
        $qb = $this->getDoctrine()->getRepository(Page::class)->getQueryToFindPublished('p');
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

    /*
     * Route("/ajax/page/{slug}", name="ajax_page", requirements={"page"="[a-zA-Z1-9\-_]+"}, methods={"POST"})
     *
    public function ajaxShow(Page $page): Response
    {
        return $this->render('page/_page.html.twig', ['page' => $page]);
    }*/
}
