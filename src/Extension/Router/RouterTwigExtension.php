<?php

namespace PiedWeb\CMSBundle\Extension\Router;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterTwigExtension extends AbstractExtension
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('homepage', [$this->router, 'generatePathForHomePage']),
            new TwigFunction('page', [$this->router, 'generatePathForPage']),
        ];
    }
}
