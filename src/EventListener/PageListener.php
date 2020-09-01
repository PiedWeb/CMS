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
        }
    }
}
