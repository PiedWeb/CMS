<?php

namespace PiedWeb\CMSBundle\Entity\PageTrait;

use PiedWeb\CMSBundle\Extension\Filter\FilterInterface;

trait PageContentTrait
{
    /** @var FilterInterface */
    protected $content;

    public function getContent()
    {
        return $this->content;
    }

    public function setContent(FilterInterface $content)
    {
        $this->content = $content;

        return $this;
    }
}
