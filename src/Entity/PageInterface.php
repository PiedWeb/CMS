<?php

namespace PiedWeb\CMSBundle\Entity;

interface PageInterface
{
    public function getRedirection();

    public function getRedirectionCode();

    public function getRealSlug();

    public function getLocale();

    public function setLocale();

    public function getChildrenPages();
}
