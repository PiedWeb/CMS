<?php

namespace App\Entity;

use PiedWeb\CMSBundle\Entity\Media as BaseMedia;
use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Repository\MediaRepository;

/**
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 */
class Media extends BaseMedia
{
}
