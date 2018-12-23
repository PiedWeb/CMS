<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Cocur\Slugify\Slugify;
use Gedmo\Mapping\Annotation as Gedmo;

trait RedirectionTrait
{
    protected $redirectionUrl;
    protected $redirectionCode;

    /**
     * Check if a content don't start by 'Location: http://valid-url.tld/eg'.
     */
    protected function manageRedirection()
    {
        $content = $this->getMainContent();
        $code = 301; // default symfony is 302...
        if ('Location:' == substr($content, 0, 9)) {
            $url = trim(substr($content, 9));
            if (preg_match('/ [1-5][0-9]{2}$/', $url, $match)) {
                $code = intval(trim($match[0]));
                $url = preg_replace('/ [1-5][0-9]{2}$/', '', $url);
            }
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $this->redirectionUrl  = $url;
                $this->redirectionCode = $code;
            }
        }

        $this->redirectionUrl  = false;
    }

    public function getRedirection()
    {
        if ($ths->redirectionUrl === null) {
            $this->manageRedirection();
        }

        return $this->redirectionUrl;
    }

    public function getRedirectionCode()
    {
        if ($ths->redirectionUrl === null) {
            $this->manageRedirection();
        }

        return $this->redirectionCode;
    }
