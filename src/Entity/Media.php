<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\MappedSuperclass
 * @Vich\Uploadable
 * UniqueEntity({"name"}, message="Ce nom existe déjà.")
 */
class Media implements TranslatableInterface, MediaInterface
{
    use IdTrait;
    use MediaTrait;
    use TranslatableTrait;

    public function __construct()
    {
        $this->updatedAt = null !== $this->updatedAt ? $this->updatedAt : new \DateTime();
        $this->createdAt = null !== $this->createdAt ? $this->createdAt : new \DateTime();
    }
}
