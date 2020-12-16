<?php

namespace PiedWeb\CMSBundle\Service\PageMainContentManager;

use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Entity\PageMainContentType;
use PiedWeb\CMSBundle\Service\App;
use Twig\Environment as Twig;

class PageMainContentManager
{
    protected $twig;
    protected $app;
    protected $markdownParser;
    protected $page;

    public function __construct(
        App $app,
        Twig $twig,
        MarkdownParserInterface $markdownParser
    ) {
        $this->app = $app;
        $this->twig = $twig;
        $this->markdownParser = $markdownParser;
    }

    public function manage(PageInterface $page): MainContentManagerInterface
    {
        $this->page = $page;

        if (PageMainContentType::MARKDOWN === $page->getMainContentType()) {
            return new Markdown($this->app, $this->twig, $page, $this->markdownParser);
        }

        return new Raw($this->app, $this->twig, $page);
    }
}
