<?php

namespace PiedWeb\CMSBundle\Extension\PageMainContentManager\ShortCode;

use PiedWeb\CMSBundle\Twig\PhoneNumberTwigTrait;
use PiedWeb\CMSBundle\Utils\HtmlBeautifer;

class Punctuation extends ShortCode
{
    public function apply($string)
    {
        $string = HtmlBeautifer::punctuationBeautifer($string);

        return $string;
    }
}
