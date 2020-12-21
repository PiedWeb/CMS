<?php

namespace PiedWeb\CMSBundle\Twig;

use PiedWeb\CMSBundle\Entity\PageInterface;
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

    public function renderChildrenListCard(Twig $twig, $page, $number = 3)
    {
        return $this->renderChildrenList($twig, $page, $number, '/component/pages_list_card.html.twig');
    }

    public function renderChildrenList(Twig $twig, PageInterface $page, $number = 3, $view = '/component/pages_list.html.twig')
    {
        $template = $this->getApp()->getView($view);

        return $twig->render($template, ['pages' => $page->getChildrenPages()]);
    }

    public function renderPagesListCard(
        Twig $twig,
        $search = '',
        int $number = 3,
        $order = 'createdAt',
        $host = null
    ) {
        return $this->renderPagesList($twig, $search, $number, $order, $host, '/component/pages_list_card.html.twig');
    }

    public function renderPagesList(
        Twig $twig,
        $search = '',
        int $number = 3,
        $order = 'createdAt',
        $host = null,
        string $view = '/component/pages_list.html.twig'
    ) {
        if (\is_string($search)) {
            $search = [['key' => 'mainContent', 'operator' => 'LIKE', 'value' => '%'.$search.'%']];
        }
        if ($this->apps->getCurrentPage()) {
            $search[] = ['key' => 'id', 'operator' => '!=', 'value' => $this->apps->getCurrentPage()->getId()];
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
