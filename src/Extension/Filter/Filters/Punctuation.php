<?php

namespace PiedWeb\CMSBundle\Extension\Filter\Filters;

use PiedWeb\CMSBundle\Utils\HtmlBeautifer;

class Punctuation extends ShortCode
{
    public function apply($string)
    {
        $string = HtmlBeautifer::punctuationBeautifer($string);

        return $string;
    }
}
