<?php

namespace PiedWeb\CMSBundle\Extension\Admin\Page;

use PiedWeb\CMSBundle\Repository\Repository;
use Sonata\AdminBundle\Controller\CRUDController as SonataCRUDController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CRUDController extends SonataCRUDController
{
    protected $params;

    public function setParams(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function listAction()
    {
        $request = $this->getRequest();
        if ($listMode = $request->get('_list_mode')) {
            $this->admin->setListMode($listMode);
        }

        $listMode = $this->admin->getListMode();
        if ('tree' === $listMode) {
            return $this->treeAction();
        }

        return parent::listAction();
    }

    public function treeAction()
    {
        $pages = Repository::getPageRepository($this->getDoctrine(), $this->params->get('pwc.entity_page'))
            ->getPagesWithoutParent();

        return $this->renderWithExtraParams('@pwcAdmin/page/page_treeView.html.twig', [
            'pages' => $pages,
            'list' => $this->admin->getList(),
            'admin' => $this->admin,
            'base_template' => $this->getBaseTemplate(),
            'action' => 'list',
        ]);
    }
}
