<?php

namespace PiedWeb\CMSBundle\Service\PageMainContentManager;

use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Service\App;
use Twig\Environment as Twig;

class Markdown extends Raw
{
    protected $markdownParser;

    public function __construct(App $app, Twig $twig, PageInterface $page, MarkdownParserInterface $markdownParser)
    {
        $this->markdownParser = $markdownParser;

        parent::__construct($app, $twig, $page);
    }

    protected function applyRendering()
    {
        parent::applyRendering(); // always apply twig rendering before markdown, avoid some errors

        foreach ($this->parts as $part) {
            $this->$part = $this->convertMarkdownImage($this->$part);
            $this->$part = $this->markdownParser->transformMarkdown($this->$part);
        }
    }
}
