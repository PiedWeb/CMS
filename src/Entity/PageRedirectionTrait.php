<?php

namespace PiedWeb\CMSBundle\Entity;

trait PageRedirectionTrait
{
    protected $redirectionUrl;
    protected $redirectionCode;

    abstract public function getMainContent(): ?string;

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
                $this->redirectionUrl = $url;
                $this->redirectionCode = $code;

                return $url;
            }
        }

        $this->redirectionUrl = false;
    }

    public function getRedirection()
    {
        if (null === $this->redirectionUrl) {
            $this->manageRedirection();
        }

        return $this->redirectionUrl;
    }

    public function getRedirectionCode()
    {
        if (null === $this->redirectionUrl) {
            $this->manageRedirection();
        }

        return $this->redirectionCode;
    }
}
