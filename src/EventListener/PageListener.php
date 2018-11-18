<?php

namespace PiedWeb\CMSBundle\EventListener;

use PiedWeb\CMSBundle\Entity\Page;

class PageListener
{
    public function preRemove(Page $page)
    {
        //$page = $args->getObject();

        if (!$page instanceof Page) {
            return;
        }

        if (!$page->getChildrenPages()->isEmpty()) {
            throw new \Exception('Action forbidden : this page have children page wich will be orphan. Look at the kids first ;-)');
        }
        // todo: plutôt que de throw an exception, modifier la page parent des pages filles pour la page parente de la page actuelle (ou rien)
    }
}
