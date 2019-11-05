<?php

namespace PiedWeb\CMSBundle\Repository;

use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;

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
            ->setHint(
                \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            );
        if (null !== $language) {
            $q->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $language);
        }

        //var_dump($q->getSql()); exit;
        $result = $q->getResult();

        return isset($result[0]) ? $result[0] : null;
    }

    public function getPagesWithoutParent()
    {
        $q = $this->createQueryBuilder('p')
            ->andWhere('p.parentPage is NULL')
            ->orderBy('p.slug', 'DESC')
            ->getQuery()
        ;

        return $q->getResult();
    }
}
