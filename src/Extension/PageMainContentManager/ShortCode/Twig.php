<?php

namespace PiedWeb\CMSBundle\Extension\PageMainContentManager\ShortCode;

use PiedWeb\CMSBundle\Twig\PhoneNumberTwigTrait;

class Twig extends ShortCode
{

    public function apply($string)
    {
        $string = $this->render($string);

        return $string;
    }

    protected function render($string)
    {
        if (! $string || strpos($string, '{') === false) {
            return $string;
        }

        $tmpl = $this->twig->createTemplate($string);
        $string = $tmpl->render(['page' => $this->page]);

        return $string;
    }
}
