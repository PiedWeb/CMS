<?php

namespace PiedWeb\CMSBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PiedWeb\CMSBundle\Entity\Media;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
}
