<?php

namespace PiedWeb\CMSBundle\Twig;

use PiedWeb\CMSBundle\Repository\Repository;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\CMSBundle\Service\AppConfig;
use Twig\Environment as Twig;

trait PageListTwigTrait
{
    /** @var string */
    protected $pageClass;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var App */
    protected $apps;

    abstract public function getApp(): AppConfig;

    public function renderPagesList(
        Twig $twig,
        $search = '',
        int $number = 3,
        $order = 'createdAt',
        string $view = '/page/_list.html.twig',
        $host = null
    ) {
        if (\is_string($search)) {
            $search = [['key' => 'mainContent', 'operator' => 'LIKE', 'value' => '%'.$search.'%']];
        }

        $order = \is_string($order) ? ['key' => $order, 'direction' => 'DESC']
            : ['key' => $order[0], 'direction' => $order[1]];

        $pages = Repository::getPageRepository($this->em, $this->pageClass)
            ->setHostCanBeNull($host ? $this->apps->isFirstApp($host) : $this->getApp()->isFirstApp())
            ->getPublishedPages($host ?? $this->getApp()->getMainHost(),  $search, $order, $number);

        $template = $this->getApp()->getView($view);

        return $twig->render($template, ['pages' => $pages]);
    }
}
