<?php

namespace PiedWeb\CMSBundle\Twig;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;

trait GalleryTwigTrait
{
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
