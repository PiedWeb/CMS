<?php

namespace PiedWeb\CMSBundle\Service;

use PiedWeb\CMSBundle\Repository\PageRepository;

class Repository
{
    public static function getPageRepository($doctrine, string $pageEntity): PageRepository
    {
        return $doctrine->getRepository($pageEntity);
    }
}
