<?php

namespace PiedWeb\CMSBundle\Twig;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Service\AppConfig;

trait GalleryTwigTrait
{
    abstract public function getApp(): AppConfig;

    public function renderGallery(Page $currentPage, $filterImageFrom = 1, $length = 1001)
    {
        $template = $this->getApp()->getView('/page/_gallery.html.twig', $this->twig);

        return $this->twig->render($template, [
            'page' => $currentPage,
            'galleryFilterFrom' => $filterImageFrom - 1,
            'length' => $length,
        ]);
    }
}
