<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Entity\SharedTrait\CustomPropertiesTrait;
use PiedWeb\CMSBundle\Entity\SharedTrait\IdTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\MappedSuperclass
 * @Vich\Uploadable
 * @ORM\HasLifecycleCallbacks
 * UniqueEntity({"name"}, message="Ce nom existe déjà.")
 */
class Media implements MediaInterface
{
    use IdTrait;
    use MediaTrait;
    use CustomPropertiesTrait;

    public function __construct()
    {
        $this->updatedAt = null !== $this->updatedAt ? $this->updatedAt : new \DateTime();
        $this->createdAt = null !== $this->createdAt ? $this->createdAt : new \DateTime();
    }
}
