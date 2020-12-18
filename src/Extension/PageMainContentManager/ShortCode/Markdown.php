<?php

namespace PiedWeb\CMSBundle\Extension\PageMainContentManager\ShortCode;

use PiedWeb\CMSBundle\Twig\PhoneNumberTwigTrait;

class MarkdownMax extends ShortCode
{

    public function apply($string)
    {
        $string = $this->render($string);

        return $string;
    }

    public function setMarkdownParser($markdownParser)
    {
        $this->markdownParser = $markdownParser;

    }

    protected function render($string)
    {
        $string = $this->markdownParser->transformMarkdown($string);
    }
}
