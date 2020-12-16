<?php

namespace PiedWeb\CMSBundle\Entity;

use PiedWeb\CMSBundle\Service\PageMainContentManager\MainContentManagerInterface;

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

    public function setMainContentType($mainContentType);

    public function setContent(MainContentManagerInterface $mainContentManager);
}
