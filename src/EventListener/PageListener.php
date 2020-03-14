<?php

namespace PiedWeb\CMSBundle\EventListener;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Service\FeedDumperService;

class PageListener
{
    protected $feedDumper;

    public function preRemove(Page $page)
    {
        if (method_exists($page, 'getChildrenPages') && !$page->getChildrenPages()->isEmpty()) {
            foreach ($page->getChildrenPages() as $cPage) {
                $cPage->setParentPage(null);
            }
        }
    }

    public function postUpdate()
    {
        $this->feedDumper->postPersist();
    }

    public function setFeedDumper(FeedDumperService $feedDumper)
    {
        $this->feedDumper = $feedDumper;
    }
}
