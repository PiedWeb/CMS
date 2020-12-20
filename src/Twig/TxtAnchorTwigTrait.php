<?php

namespace PiedWeb\CMSBundle\Twig;

use Cocur\Slugify\Slugify;

trait TxtAnchorTwigTrait
{
    public function renderTxtAnchor($name)
    {
        $template = $this->getApp()->getView('/component/txt_anchor.html.twig', $this->twig);

        $slugify = new Slugify();
        $name = $slugify->slugify($name);

        return $this->twig->render($template, ['name' => $name]);
    }
}
