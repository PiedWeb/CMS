<?php

namespace PiedWeb\CMSBundle\Entity;

use PiedWeb\CMSBundle\Extension\Filter\FilterInterface;

interface PageInterface
{
    public function getId();

    public function getSlug();

    public function getRedirection();

    public function getRedirectionCode();

    public function getRealSlug();

    public function getLocale();

    public function setLocale($locale);

    public function getChildrenPages();

    public function getMainContent();

    public function getCreatedAt();

    public function getHost();

    public function getTemplate();

    public function setMainContent(?string $mainContent);

    public function getCustomProperty(string $name);

    public function getH1();

    public function getTitle();

    public function getName();

    public function setContent(FilterInterface $mainContentManager);

    public function setHost($host);
}
