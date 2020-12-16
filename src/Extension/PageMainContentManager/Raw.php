<?php

namespace PiedWeb\CMSBundle\Extension\PageMainContentManager;

use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\CMSBundle\Utils\HtmlBeautifer;
use TOC\MarkupFixer;
use TOC\TocGenerator;
use Twig\Environment as Twig;

// TODO remove APP and remove PAGE to use it on what i want string (like in a twig extension)
class Raw implements MainContentManagerInterface
{
    protected $parts = ['chapeau', 'intro', 'toc', 'content', 'postContent'];

    /**
     * @var Twig
     */
    protected $twig;

    /**
     * @var App
     *          Required only for markdown image render...
     *          remove it and set template in page
     */
    protected $app;

    protected $page;
    protected $chapeau = '';
    protected $intro = '';
    protected $toc = '';
    protected $content = '';
    protected $postContent = '';

    protected $parsed = false;

    public function __construct(App $app, Twig $twig, PageInterface $page)
    {
        $this->page = $page;
        $this->app = $app->switchCurrentApp($page->getHost());
        $this->twig = $twig;
    }

    protected function parse()
    {
        if (true === $this->parsed) {
            return;
        }

        $this->parseContentBeforeRendering();
        $this->applyRendering();
        $this->parseContentAfterRendering();
    }

    protected function applyRendering()
    {
        foreach ($this->parts as $part) {
            if ($this->page->mustParseTwig()) {
                $this->$part = $this->render($this->$part);
            }
            $this->$part = HtmlBeautifer::punctuationBeautifer($this->$part);
        }
    }

    protected function parseContentBeforeRendering()
    {
        $originalContent = (string) $this->page->getMainContent();

        $parsedContent = explode('<!--break-->', $originalContent, 3);

        $this->chapeau = isset($parsedContent[1]) ? $parsedContent[0] : '';
        $this->postContent = $parsedContent[2] ?? '';
        $this->content = $parsedContent[1] ?? $parsedContent[0];
    }

    protected function parseContentAfterRendering()
    {
        $this->parseToc();
    }

    protected function parseToc()
    {
        $this->content = (new MarkupFixer())->fix($this->content); // this work only on good html

        // this is a bit crazy
        $content = $this->content;
        $content = explode('<h', $content, 2);
        //var_dump($content);exit;
        if (isset($content[1])) {
            $this->intro = $content[0];
            $this->content = '<h'.$content[1];
        } else {
            $this->content = $content[0];
        }

        if ($this->page->getOtherProperty('toc')) {
            $this->toc = (new TocGenerator())->getHtmlMenu($this->content);
        }
    }

    public function getBody(bool $withChapeau = false)
    {
        $this->parse();

        return ($withChapeau ? $this->chapeau : '').$this->intro.$this->content.$this->postContent;
    }

    public function getChapeau()
    {
        $this->parse();

        return $this->chapeau;
    }

    public function getContent()
    {
        $this->parse();

        return $this->content;
    }

    public function getPostContent()
    {
        $this->parse();

        return $this->postContent;
    }

    public function getIntro()
    {
        $this->parse();

        return $this->intro;
    }

    public function getToc()
    {
        $this->parse();

        return $this->toc;
    }

    public function convertMarkdownImage(string $body)
    {
        preg_match_all('/(?:!\[(.*?)\]\((.*?)\))/', $body, $matches);

        if (! isset($matches[1])) {
            return;
        }

        $nbrMatch = \count($matches[0]);
        for ($k = 0; $k < $nbrMatch; ++$k) {
            $renderImg = $this->twig->render(
                $this->app->getTemplate('/component/_inline_image.html.twig', $this->twig),
                [
                    //"image_wrapper_class" : "mimg",'
                    'image_src' => $matches[2][$k],
                    'image_alt' => htmlspecialchars($matches[1][$k]),
            ]
            );
            $body = str_replace($matches[0][$k], $renderImg, $body);
        }

        return $body;
    }

    protected function render($string)
    {
        if (! $string) {
            return '';
        }

        $tmpl = $this->twig->createTemplate(HtmlBeautifer::removeHtmlComments($string));
        $string = $tmpl->render(['page' => $this->page]);

        return $string;
    }
}
