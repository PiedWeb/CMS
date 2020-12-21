<?php

namespace PiedWeb\CMSBundle\Extension\Filter\Filters;

use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Service\AppConfig;
use Twig\Environment as Twig;

abstract class ShortCode implements ShortCodeInterface
{
    /** @var Twig */
    protected $twig;

    /** @var AppConfig */
    protected $app;

    protected $page;

    public function __construct(Twig $twig, AppConfig $app, PageInterface $page)
    {
        $this->twig = $twig;
        $this->app = $app;
        $this->page = $page;
    }

    abstract public function apply($string);

    public function getApp(): AppConfig
    {
        return $this->app;
    }
}
