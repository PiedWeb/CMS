<?php

namespace PiedWeb\CMSBundle\Repository;

/**
 * todo implement interface when needed
 * Useful for avoiding intelephense error.
 */
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
