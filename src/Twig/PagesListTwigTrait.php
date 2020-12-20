<?php

namespace PiedWeb\CMSBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Repository\PageRepository;
use PiedWeb\CMSBundle\Service\AppConfig;
use Twig\Environment as Twig;

trait PagesListTwigTrait
{
    /** @var AppConfig */
    protected $app;

    /** @var EntityManagerInterface */
    protected $em;

    abstract public function getApp(): AppConfig;

    public function renderPagesList(
        Twig $twig,
        $search = '',
        int $number = 3,
        $order = ['createdAt', 'DESC'],
        string $template = '@PiedWebCMS/page/_pages_list.html.twig',
        $host = null
    ) {
        /** @var PageRepository */
        $pageRepo = $this->em->getRepository($this->page_class);
        $queryBuilder = $pageRepo->getQueryToFindPublished('p');
        if ($search) { // TODO/IDEA search with more option
            $queryBuilder->andWhere('p.mainContent LIKE :containing')->setParameter('containing', '%'.$search.'%');
        }

        $host ? $pageRepo->andHost($queryBuilder, $host)
            : $pageRepo->andHost($queryBuilder, $this->getApp()->getMainHost(), $this->getApp()->isFirstApp());

        if (\is_string($order)) {
            $queryBuilder->orderBy('p.'.$order, 'DESC');
        } else {
            $queryBuilder->orderBy('p.'.$order[0], $order[1]);
        }
        $queryBuilder->setMaxResults($number); // TODO Add pagination

        $pages = $queryBuilder->getQuery()->getResult();

        return $twig->render($template, ['pages' => $pages]);
    }
}
