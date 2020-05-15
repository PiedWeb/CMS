<?php

namespace PiedWeb\CMSBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function getQueryToFindPublished($p)
    {
        return $this->createQueryBuilder($p)
            ->andWhere($p.'.createdAt <=  :nwo')
            ->setParameter('nwo', new \DateTime())
            ->orderBy($p.'.createdAt', 'DESC');
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
}
