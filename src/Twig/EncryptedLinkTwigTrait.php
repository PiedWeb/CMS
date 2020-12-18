<?php

namespace PiedWeb\CMSBundle\Twig;

use PiedWeb\CMSBundle\Extension\PageMainContentManager\EncryptedLink as Encrypter;

trait EncryptedLinkTwigTrait
{
    public function renderEncryptedLink($anchor, $path, $attr = [])
    {
        $attr = array_merge($attr, ['data-rot' => Encrypter::encrypt($path)]);
        $template = $this->getApp()->getTemplate('/component/javascript_link.html.twig', $this->twig);
        $renderedLink = $this->twig->render($template, ['anchor' => $anchor, 'attr' => $attr]);

        return $renderedLink;
    }
}
