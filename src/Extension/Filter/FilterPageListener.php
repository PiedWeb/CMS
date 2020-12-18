<?php

namespace PiedWeb\CMSBundle\Extension\Filter;

use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\CMSBundle\Service\AppConfig;
use Twig\Environment as Twig;

class FilterPageListener
{
    protected $apps;
    protected $firstPageEverLoaded = false;
    protected $twig;
    protected $app;
    protected $markdownParser;

    public function __construct(
        App $app,
        Twig $twig,
        MarkdownParserInterface $markdownParser,
        App $apps
    ) {
        $this->app = $app;
        $this->twig = $twig;
        $this->markdownParser = $markdownParser;
        $this->apps = $apps;
    }

    public function postLoad(Page $page)
    {
        $app = $this->apps->get();

        if (! $this->firstPageEverLoaded) {
            $app = $this->apps->switchCurrentApp($page)->get();
            $this->firstPageEverLoaded = true;
        }

        $this->checkTwigShortcodeAuth($app, $page);

        $manager = new Raw($this->app, $this->twig, $this->markdownParser, $page);
        $page->setContent($manager);
    }

    /**
     * if twig shortcode is disabled for app, we avoid the page add it (some kind of hack attempt ?!).
     */
    protected function checkTwigShortcodeAuth(AppConfig $app, Page $page)
    {
        if (false === $app->canUseTwigShortcode()) {
            $main_content_shortcode = $page->getOtherProperty('main_content_shortcode');
            if (self::containTwigShortcode($main_content_shortcode)) {
                throw new \Exception('Can\'t use twig shortcode, disabled in app config !');
                //. or preg_replace('/(^twig,|,twig,|,twig$)/i', '', $main_content_shortcode);
            }
            $fields_shortcode = $page->getOtherProperty('fields_shortcode');
            if (self::containTwigShortcode($fields_shortcode)) {
                throw new \Exception('Can\'t use twig shortcode, disabled in app config !');
            }
        }
    }

    protected static function containTwigShortcode($shortcode)
    {
        if (\is_string($shortcode)) {
            $shortcode = explode(',', $shortcode);
        }

        return \in_array('twig', $shortcode);
    }
}
