<?php

namespace PiedWeb\CMSBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method list<T>   findAll()
 * @method list<T>   findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface PageRepositoryInterface extends ServiceEntityRepositoryInterface
{
    public function getPublishedPages(string $host = '', array $where = [], array $orderBy = [], int $limit = 0);

    public function getPage($slug, $host, $hostCanBeNull): ?Page;

    public function getIndexablePages(
        $host,
        $hostCanBeNull,
        $locale,
        $defaultLocale,
        ?int $limit = null
    ): QueryBuilder;

    public function getPagesWithoutParent();

    public function setHostCanBeNull($hostCanBeNull);

    public function getPagesUsingMedia($media);
}
