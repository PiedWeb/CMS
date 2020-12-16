<?php

namespace PiedWeb\CMSBundle\Entity;

use PiedWeb\CMSBundle\Extension\PageMainContentManager\MainContentManagerInterface;

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

    public function mainContentIsMarkdown();

    public function getCreatedAt();

    public function getHost();

    public function getTemplate();

    public function setMainContent(?string $mainContent);

    public function getOtherProperty($name);

    public function mustParseTwig(): bool;

    public function setTwig($twig);

    public function getMainContentType();

    public function getH1();

    public function getTitle();

    public function getName();

    public function setMainContentType($mainContentType);

    public function setContent(MainContentManagerInterface $mainContentManager);

    public function setHost($host);
}
