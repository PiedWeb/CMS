<?php

namespace PiedWeb\CMSBundle\Service;

use PiedWeb\CMSBundle\Entity\PageInterface;
use Twig\Environment as Twig;

class MainContentManager
{
    protected $page;
    protected $chapeau;
    protected $mainContent;

    public function __construct(Twig $twig, PageInterface $page)
    {
        $this->page = $page;
        $this->twig = $twig;

        $parsedContent = explode('<!--break-->', (string) $this->page->getMainContent());

        $this->chapeau = isset($parsedContent[1]) ? $parsedContent[0] : null;
        $this->mainContent = $parsedContent[1] ?? $parsedContent[0];

        if ($this->page->mainContentIsMarkdown()) {
            if ($this->chapeau) {
                $this->chapeau = '{% filter markdown %}'.$this->chapeau.'{% endfilter %}';
            }
            if ($this->mainContent) {
                $this->mainContent = self::convertMarkdownImage($this->mainContent);
                $this->mainContent = '{% filter markdown %}'.$this->mainContent.'{% endfilter %}';
            }
        }
    }

    public static function removeHtmlComments(string $content)
    {
        return preg_replace('/<!--(.|\s)*?-->/', '', $content);
    }

    public static function punctuationBeautifer($text)
    {
        return str_replace(
            [' ;', ' :', ' ?', ' !', '« ', ' »', '&laquo; ', ' &raquo;'],
            ['&nbsp;;', '&nbsp;:', '&nbsp;?', '&nbsp;!', '«&nbsp;', '&nbsp;»', '&laquo;&nbsp;', '&nbsp;&raquo;'],
            $text
        );
    }

    public static function convertMarkdownImage(string $body)
    {
        return preg_replace(
            '/(?:!\[(.*?)\]\((.*?)\))/',
            '{%'
            .PHP_EOL.'    include "@PiedWebCMS/component/_inline_image.html.twig" with {'
            .PHP_EOL.'        "image_wrapper_class" : "mimg",'
            .PHP_EOL.'        "image_src" : "$2",'
            .PHP_EOL.'        "image_alt" : "$1"'
            .PHP_EOL.'    } only'
            .PHP_EOL.'%}'.PHP_EOL,
            $body
        );
    }

    protected function render($string)
    {
        $string = $this->twig->createTemplate($string)->render(['page' => $this->page]); // convert to html
        $string = self::punctuationBeautifer($string); // small fixes on punctuation to avoid linebreak
        $string = self::removeHtmlComments($string);

        return $string;
    }

    public function getFull()
    {
        return $this->render($this->chapeau.chr(10).chr(10).$this->mainContent);
    }

    public function getChapeau()
    {
        return $this->render($this->chapeau);
    }

    public function getMainContent()
    {
        return $this->render($this->mainContent);
    }

    public function getContent()
    {
        return $this->render($this->mainContent);
    }

    public function getIntro()
    {
        // return text without chapeau before first <h
    }

    public function getContentWithoutIntro()
    {
    }

    public function getToc()
    {
    }
}
