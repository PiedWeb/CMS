<?php

namespace PiedWeb\CMSBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;

/**
 * @ORM\Entity(repositoryClass="PiedWeb\CMSBundle\Repository\MediaRepository")
 * @Vich\Uploadable
 * UniqueEntity({"name"}, message="Ce nom existe déjà.")
 */
class Media implements TranslatableInterface
{
    use IdTrait, MediaTrait, TimestampableEntity, TranslatableTrait;


    public function __construct()
    {
        $this->updatedAt = $this->updatedAt !== null ? $this->updatedAt : new \DateTimeImmutable();
        $this->createdAt = $this->createdAt !== null ? $this->createdAt : new \DateTimeImmutable();
    }
}
