<?php

namespace PiedWeb\CMSBundle\EventListener;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;

class PageListener
{
    public function preRemove(Page $page)
    {
        if (method_exists($page, 'getChildrenPages') && !$page->getChildrenPages()->isEmpty()) {
            foreach ($page->getChildrenPages() as $cPage) {
                $cPage->setParentPage(null);
            }
            //throw new \Exception('Action forbidden : this page have children page wich will be orphan.');
        }
        // todo: plutôt que de throw an exception, modifier la page parent des pages filles pour la page parente de
        // la page actuelle (ou rien)
    }
}
