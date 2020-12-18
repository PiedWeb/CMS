<?php

namespace PiedWeb\CMSBundle\Extension\Filter;

use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use PiedWeb\CMSBundle\Entity\PageInterface;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\CMSBundle\Service\AppConfig;
use TOC\MarkupFixer;
use TOC\TocGenerator;
use Twig\Environment as Twig;

class Raw implements FilterInterface
{
    protected $parts = ['chapeau', 'intro', 'toc', 'content', 'postContent'];

    /** @var Twig */
    protected $twig;

    /** @var MarkdownParserInterface */
    protected $markdownParser;

    /** @var AppConfig */
    protected $app;

    protected $page;
    protected $chapeau = '';
    protected $intro = '';
    protected $toc = '';
    protected $content = '';
    protected $postContent = '';

    protected $parsed = false;

    public function __construct(App $app, Twig $twig, MarkdownParserInterface $markdownParser, PageInterface $page)
    {
        $this->page = $page;
        $this->app = $app->switchCurrentApp($page->getHost())->get();
        $this->twig = $twig;
        $this->markdownParser = $markdownParser;
    }

    protected function parse()
    {
        if (true === $this->parsed) {
            return;
        }

        $this->content = (string) $this->page->getMainContent();
        $this->parseContentBeforeSplitting();
        $this->splitting();
        $this->parseContentAfterSplitting();
    }

    protected function splitting()
    {
        $parsedContent = explode('<!--break-->', $this->content, 3);

        $this->chapeau = isset($parsedContent[1]) ? $parsedContent[0] : '';
        $this->postContent = $parsedContent[2] ?? '';
        $this->content = $parsedContent[1] ?? $parsedContent[0];
    }

    protected function parseContentBeforeSplitting()
    {
        $this->content = $this->applyShortCodeOn($this->content, 'main_content_shortcode');
    }

    protected function parseContentAfterSplitting()
    {
        if (null !== $this->page->getOtherProperty('toc')) {
            $this->parseToc();
        }
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

    /**
     * Magic getter for Page properties.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (! preg_match('/^get/', $method)) {
            $method = 'get'.ucfirst($method);
        }

        return $this->pageCall($method, $arguments);
    }

    protected function pageCall($method, $arguments)
    {
        if ($arguments) {
            return \call_user_func_array([$this->page, $method], $arguments);
        }

        return $this->page->$method();
    }

    // TODO : move Short Code Converter to somewhere else (like apply renderingFilters on each fields from Page ?!)
    public function h1()
    {
        return $this->filterField($this->page->getH1() ?: $this->page->getTitle());
    }

    public function title($firstH1 = false)
    {
        if ($firstH1) {
            return $this->h1();
        }

        return $this->filterField($this->page->getTitle() ?: $this->page->getH1());
    }

    public function name(): ?string
    {
        $name = $this->page->getName();
        $names = explode(',', $name);

        return $this->filterField($names[0] ? trim($names[0]) : (null !== $name ? $name : $this->getH1()));
    }

    protected function filterField($field)
    {
        $field = $this->applyShortCodeOn($field, 'fields_shortcode');

        return $field;
    }

    protected function applyShortCodeOn($field, string $shortcodesLabel)
    {
        $shortcodes = $this->app->getCustomProperty($shortcodesLabel);
        if (! $shortcodes) {
            return $field;
        }

        $shortcodes = \is_string($shortcodes) ? explode(',', $shortcodes) : $shortcodes;
        foreach ($shortcodes as $shortcode) {
            if (false === strpos($shortcode, '/')) {
                $shortcode = 'PiedWeb\CMSBundle\Extension\Filter\Filters\\'.ucfirst($shortcode);
            }

            $filter = new $shortcode($this->twig, $this->app, $this->page);
            if ('PiedWeb\CMSBundle\Extension\Filter\Filters\Markdown' == $shortcode) {
                $filter->setMarkdownParser($this->markdownParser);
            }
            $field = $filter->apply($field);
        }

        return $field;
    }
}
