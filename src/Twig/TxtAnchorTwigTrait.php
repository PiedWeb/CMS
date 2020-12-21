<?php

namespace PiedWeb\CMSBundle\Twig;

use Cocur\Slugify\Slugify;
use PiedWeb\CMSBundle\Service\AppConfig;

trait TxtAnchorTwigTrait
{
    abstract public function getApp(): AppConfig;

    public function renderTxtAnchor($name)
    {
        $template = $this->getApp()->getView('/component/txt_anchor.html.twig', $this->twig);

        $slugify = new Slugify();
        $name = $slugify->slugify($name);

        return $this->twig->render($template, ['name' => $name]);
    }
}
