<?php

namespace PiedWeb\CMSBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method list<T>   findAll()
 * @method list<T>   findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository implements PageRepositoryInterface
{
    protected $hostCanBeNull = false;

    public function getPublishedPages(string $host = '', array $where = [], array $orderBy = [], int $limit = 0)
    {
        $qb = $this->getQueryToFindPublished('p');

        if ($host) {
            $this->andHost($qb, $host, $this->hostCanBeNull);
        }

        foreach ($where as $k => $w) {
            $qb->andWhere('p.'.$w['key'].' '.$w['operator'].' :m'.$k)->setParameter('m'.$k, $w['value']);
        }

        if ($orderBy) {
            $qb->orderBy('p.'.$orderBy['key'], $orderBy['direction']);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    protected function getQueryToFindPublished($p): QueryBuilder
    {
        return $this->createQueryBuilder($p)
            ->andWhere($p.'.createdAt <=  :nwo')
            ->setParameter('nwo', new \DateTime())
            ->orderBy($p.'.createdAt', 'DESC');
    }

    public function getPage($slug, $host, $hostCanBeNull): ?Page
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.slug =  :slug')->setParameter('slug', $slug);

        if ((int) $slug > 0) {
            $qb->orWhere('p.id =  :slug')->setParameter('slug', $slug);
        }

        $qb = $this->andHost($qb, $host, $hostCanBeNull);

        return $qb->getQuery()->getResult()[0] ?? null;
    }

    protected function andNotRedirection(QueryBuilder $qb): QueryBuilder
    {
        return $qb->andWhere('p.mainContent IS NULL OR p.mainContent NOT LIKE :noi')
            ->setParameter('noi', 'Location:%');
    }

    protected function andIndexable(QueryBuilder $qb): QueryBuilder
    {
        return $qb->andWhere('p.metaRobots IS NULL OR p.metaRobots NOT LIKE :noi2')
            ->setParameter('noi2', '%noindex%');
    }

    public function andHost(QueryBuilder $qb, $host, $hostCanBeNull = false): QueryBuilder
    {
        return $qb->andWhere('(p.host = :h '.($hostCanBeNull ? ' OR p.host IS NULL' : '').')')
            ->setParameter('h', $host);
    }

    protected function andLocale(QueryBuilder $qb, $locale, $defaultLocale): QueryBuilder
    {
        return $qb->andWhere(($defaultLocale == $locale ? 'p.locale IS NULL OR ' : '').'p.locale LIKE :locale')
                ->setParameter('locale', $locale);
    }

    /**
     * Return page for sitemap
     * $qb->getQuery()->getResult();.
     */
    public function getIndexablePages(
        $host,
        $hostCanBeNull,
        $locale,
        $defaultLocale,
        ?int $limit = null
    ): QueryBuilder {
        $qb = $this->getQueryToFindPublished('p');
        $qb = $this->andIndexable($qb);
        $qb = $this->andNotRedirection($qb);
        $qb = $this->andHost($qb, $host, $hostCanBeNull);
        $qb = $this->andLocale($qb, $locale, $defaultLocale);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    public function getPagesWithoutParent()
    {
        $q = $this->createQueryBuilder('p')
            ->andWhere('p.parentPage is NULL')
            ->orderBy('p.slug', 'DESC')
            ->getQuery();

        return $q->getResult();
    }

    public function getPagesUsingMedia($media)
    {
        $q = $this->createQueryBuilder('p')
            ->andWhere('p.mainContent LIKE :val')
            ->setParameter('val', '%'.$media.'%')
            ->getQuery()
        ;

        return $q->getResult();
    }

    /**
     * Set the value of hostCanBeNull.
     *
     * @return self
     */
    public function setHostCanBeNull($hostCanBeNull)
    {
        $this->hostCanBeNull = $hostCanBeNull;

        return $this;
    }
}
