<?php

namespace PiedWeb\CMSBundle\Twig;

use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Extension\Router\RouterInterface;

trait EncryptedLinkTwigTrait
{
    /** @var RouterInterface */
    private $router;

    public function renderEncryptedLink($anchor, $path, $attr = [])
    {
        if ($path instanceof PageInterface) {
            $path = $this->router->generate($path);
        }

        $attr = array_merge($attr, ['data-rot' => self::encrypt($path)]);
        $template = $this->getApp()->getView('/component/javascript_link.html.twig', $this->twig);
        $renderedLink = $this->twig->render($template, ['anchor' => $anchor, 'attr' => $attr]);

        return $renderedLink;
    }

    public static function encrypt($path)
    {
        if (0 === strpos($path, 'http://')) {
            $path = '-'.substr($path, 7);
        } elseif (0 === strpos($path, 'https://')) {
            $path = '_'.substr($path, 8);
        } elseif (0 === strpos($path, 'mailto:')) {
            $path = '@'.substr($path, 7);
        }

        return str_rot13($path);
    }
}
