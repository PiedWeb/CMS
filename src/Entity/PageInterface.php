<?php

namespace PiedWeb\CMSBundle\Entity;

interface PageInterface
{
    public function getSlug();

    public function getRedirection();

    public function getRedirectionCode();

    public function getRealSlug();

    public function getLocale();

    public function setLocale($locale);

    public function getChildrenPages();

    public function getMainContent();

    public function mainContentIsMarkdown();

    public function getCreatedAt();

    public function getHost();
}
