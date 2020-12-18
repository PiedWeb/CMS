<?php

namespace PiedWeb\CMSBundle\Extension\Filter\Filters;

use PiedWeb\CMSBundle\Twig\EmailTwigTrait;

class Email extends ShortCode
{
    use EmailTwigTrait;

    public function apply($string)
    {
        $string = $this->convertEmail($string);

        return $string;
    }

    public function convertEmail($body)
    {
        $body = $this->convertRawEncodeMail($body);
        $body = $this->convertTwigEncodedMail($body);

        return $body;
    }

    // Todo Move it to a dedicatedShortcode
    public function convertTwigEncodedMail($body)
    {
        $rgx = '/{{\s*e?mail\(((?<![\\\])[\'"])((?:.(?!(?<![\\\])\1))*.?)\1\)\s*}}/iU';
        preg_match_all($rgx, $body, $matches);

        if (! isset($matches[2])) {
            return $body;
        }

        $nbrMatch = \count($matches[0]);
        for ($k = 0; $k < $nbrMatch; ++$k) {
            $body = str_replace($matches[0][$k], $this->renderEncodedMail($this->twig, $matches[2][$k]), $body);
        }

        return $body;
    }

    public function convertRawEncodeMail($body)
    {
        $rgx = '/\[((?:[a-z0-9!#$%&*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&*+=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f'
            .'\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)'
            .'+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}'
            .'(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21'
            .'-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\]))\]/iU';
        preg_match_all($rgx, $body, $matches);

        $nbrMatch = \count($matches[0]);
        for ($k = 0; $k < $nbrMatch; ++$k) {
            $body = str_replace($matches[0][$k], $this->renderEncodedMail($this->twig, $matches[1][$k]), $body);
        }

        return $body;
    }
}
