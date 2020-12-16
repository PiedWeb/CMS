<?php

namespace PiedWeb\CMSBundle\EventListener;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\CMSBundle\Service\PageMainContentManager\PageMainContentManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PageListener
{
    protected $params;
    protected $mainContentManager;

    public function __construct(ParameterBagInterface $params, PageMainContentManager $mainContentManager)
    {
        $this->params = $params;
        //$this->app = new App(null, $this->params);
        $this->mainContentManager = $mainContentManager;
    }

    public function postLoad(Page $page)
    {
        // todo
        //$this->switchCurrentApp($page->getHost());
        //$page->setApp(clone $this->app);

        if (false === $this->params->get('pwc.main_content_twig') || null === $page->getOtherProperty('twig')) {
            $page->setTwig($this->params->get('pwc.main_content_twig'));
        }

        if (null === $page->getMainContentType()) {
            $page->setMainContentType($this->params->get('pwc.main_content_type_default'));
        }

        $page->setContent($this->mainContentManager->manage($page));
    }

    public function preRemove(Page $page)
    {
        // method_exists($page, 'getChildrenPages') &&
        if (!$page->getChildrenPages()->isEmpty()) {
            foreach ($page->getChildrenPages() as $cPage) {
                $cPage->setParentPage(null);
            }
        }
    }
}
