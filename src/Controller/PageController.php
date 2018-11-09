<?php

namespace PiedWeb\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use PiedWeb\CMSBundle\Entity\Page;

class PageController extends AbstractController
{

    public function home(TranslatorInterface $translator)
    {
        $page = $this->getDoctrine()->getRepository(Page::class)->findOneBy(['slug' => 'homepage']);

        if (!$page) {
            $page = new Page();
            $page->setTitle($translator->trans('installation.new.title'))
                 ->setExcrept($translator->trans('installation.new.text'));
            return $this->show($page);
        }

        return $this->show($page);
    }

    public function show(Page $page): Response
    {
        if ($page->getSlug() == 'homepage') {
            $this->redirectToRoute('home');
        }

        return $this->render('page/page.html.twig', ['page' => $page]);
    }

    public function showList(Page $page, $render = '@PiedWebCMSBundle/page/_list.html.twig', $limit = 100;)
    {
        $page = $this->getDoctrine()->getRepository(Page::class)->createQueryBuilder('p')
            ->andWhere('p.id != :id')
            ->setParameter('id', $page->getId());
        $qb->orderBy('p.createdAt', 'DESC');
        $qb->setMaxResults( $limit );
        $qb->getQuery();

        $pages = $qb->execute();

        return $this->render($render, ['pages' => $pages]);
    }


    /*
     * @Route("/ajax/page/{slug}", name="ajax_page", requirements={"page"="[a-zA-Z1-9\-_]+"}, methods={"POST"})
     *
    public function ajaxShow(Page $page): Response
    {
        return $this->render('page/_page.html.twig', ['page' => $page]);
    }*/

}
