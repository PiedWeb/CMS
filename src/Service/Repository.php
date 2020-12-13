<?php

namespace PiedWeb\CMSBundle\Service;

use PiedWeb\CMSBundle\Repository\MediaRepository;
use PiedWeb\CMSBundle\Repository\PageRepository;

// todo implement interface when needed
class Repository
{
    public static function getPageRepository($doctrine, string $pageEntity): PageRepository
    {
        return $doctrine->getRepository($pageEntity);
    }

    public static function getMediaRepository($doctrine, string $pageEntity): MediaRepository
    {
        return $doctrine->getRepository($pageEntity);
    }
}
