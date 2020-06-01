<?php

namespace PiedWeb\CMSBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;

class PageAdminController extends CRUDController
{
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
        $pages = $this->getDoctrine()
            ->getRepository($this->container->getParameter('pwc.entity_page'))
            ->getPagesWithoutParent();

        return $this->renderWithExtraParams('@PiedWebCMS/admin/page_treeView.html.twig', [
            'pages' => $pages,
            'list' => $this->admin->getList(),
            'admin' => $this->admin,
            'base_template' => $this->getBaseTemplate(),
            'action' => 'list',
        ]);
    }
}
