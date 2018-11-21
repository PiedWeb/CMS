<?php

namespace PiedWeb\CMSBundle\Repository;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry, string $entity)
    {
        parent::__construct($registry, $entity);
    }

    public function getQueryToFindPublished($p)
    {
        return $this->createQueryBuilder($p)
            ->andWhere($p.'.createdAt <=  :nwo')
            ->setParameter('nwo', new \DateTime())
            ->orderBy($p.'.createdAt', 'DESC')
        ;
    }

    public function findOneBySlug($slug, $language = null)
    {
        $q = $this->createQueryBuilder('p')
            ->andWhere('p.slug = :val')
            ->setParameter('val', $slug)
            ->setMaxResults(1)
            ->getQuery()
            ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
        ;
        if (null !== $language) {
            $q->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $language);
        }

        //var_dump($q->getSql()); exit;
        $result = $q->getResult();

        return isset($result[0]) ? $result[0] : null;
    }

//    /**
//     * @return Page[] Returns an array of Page objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Page
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
