<?php

namespace PiedWeb\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use PiedWeb\CMSBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use \Gedmo\Translatable\TranslatableListener;

class PageController extends AbstractController
{
    protected $translator;

    public function show(string $slug = 'homepage', Request $request, TranslatorInterface $translator)
    {
        $page = $this->getDoctrine()->getRepository(Page::class)->findOneBySlug($slug == '' ? 'homepage' : $slug, $request->getLocale());

        // Check if page exist
        if ($page === null && $slug == 'homepage') {
            $page = new Page();
            $page->setTitle($translator->trans('installation.new.title'))
                 ->setExcrept($translator->trans('installation.new.text'));
            return $this->render('@PiedWebCMS/page/page.html.twig', ['page' => $page]);
        } elseif ($page === null) {
            throw $this->createNotFoundException($translator->trans('page.not_found'));
        }

        // Check if page is public
        if($page->getCreatedAt() > new \DateTimeImmutable() && !$this->isGranted('ROLE_ADMIN')) {
            throw new NotFoundHttpException('Sorry not existing!');
        }

        $redirect = $this->checkIfUriIsCanonical($request, $page);
        if ($redirect !== false) {
            return $this->redirect($redirect[0], $redirect[1]);
        }

        $redirect = $this->getRedirection($page);
        if ($redirect !== false) {
            return $this->redirect($redirect[0], $redirect[1]);
        }

        $template = method_exists(Page::class, 'getTemplate') && $page->getTemplate() !== null ? $page->getTemplate() : '@PiedWebCMS/page/page.html.twig';
        // transfer '@PiedWebCMS/page/page.html.twig' to config file :)

        return $this->render($template, ['page' => $page]);
    }

    protected function checkIfUriIsCanonical($request, $page)
    {

        $real = $request->getRequestUri();

        $defaultLocale = $this->container->getParameter('locale');

        $expected = $page->getSlug()=='homepage' ?
            // rtrim to keep homepage on mydomain.tld more than mydomain.tld/default-locale/
            // rtrim($this->get('router')->generate('piedweb_cms_page'), $defaultLocale.'/')
            $this->get('router')->generate('piedweb_cms_page') :
            $this->get('router')->generate('piedweb_cms_page', ['slug'=>$page->getRealSlug()])
        ;

        /**
        echo '<pre>';
        var_dump($this->get('router')->generate('piedweb_cms_page'));
        var_dump(rtrim($this->get('router')->generate('piedweb_cms_page'), $defaultLocale.'/'));
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

    public function showList(?Page $page, $render = '@PiedWebCMS/page/_list.html.twig', $limit = 100)
    {
        $qb = $this->getDoctrine()->getRepository(Page::class)->getQueryToFindPublished('p');
        if ($page !== null) {
            $qb->andWhere('p.parentPage = :id');
            $qb->setParameter('id', $page->getId());
        }
        $qb->orderBy('p.createdAt', 'DESC');
        $qb->setMaxResults($limit);

        $pages = $qb->getQuery()->getResult();

        return $this->render($render, ['pages' => $pages]);
    }

    /**
     * Check if a content don't start by 'Location: http://valid-url.tld/eg'
     */
    protected function getRedirection($page)
    {
        $content = $page->getMainContent();
        $code = 301; // default symfony is 302...
        if (substr($content, 0, 9) == 'Location:') {
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
