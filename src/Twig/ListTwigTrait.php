<?php

namespace PiedWeb\CMSBundle\Twig;

use PiedWeb\CMSBundle\Repository\Repository;

trait ListTwigTrait
{
    public function renderPagesList(
        string $containing = '',
        int $number = 3,
        string $orderBy = 'createdAt',
        string $template = '@PiedWebCMS/page/_pages_list.html.twig'
    ) {
        $pages = Repository::getPageRepository($this->em, $this->pageClass)
            ->setHostCanBeNull($this->getApp()->isFirstApp())
            ->getPublishedPages(
                $this->getApp()->getMainHost(),
                [['key' => 'mainContent', 'operator' => 'LIKE', 'value' => '%'.$containing.'%']],
                ['key' => $orderBy, 'direction' => 'DESC'],
                $number
            );

        return $this->twig->render($template, ['pages' => $pages]);
    }
}
