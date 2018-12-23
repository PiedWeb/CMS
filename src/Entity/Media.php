<?php

namespace PiedWeb\CMSBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;

/**
 * @ORM\MappedSuperclass
 * @Vich\Uploadable
 * UniqueEntity({"name"}, message="Ce nom existe déjà.")
 */
class Media implements TranslatableInterface, MediaInterface
{
    use IdTrait, MediaTrait, TranslatableTrait;

    public function __construct()
    {
        $this->updatedAt = null !== $this->updatedAt ? $this->updatedAt : new \DateTime();
        $this->createdAt = null !== $this->createdAt ? $this->createdAt : new \DateTime();
    }
}
