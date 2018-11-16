<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait PageImageTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\Media")
     */
    private $mainImage;

    /**
     * @ORM\ManyToMany(targetEntity="PiedWeb\CMSBundle\Entity\Media")
     */
    private $images;

    public function __construct_image()
    {
        $this->images = new ArrayCollection();
    }

    public function getMainImage(): ?Media
    {
        return $this->mainImage;
    }

    public function setMainImage(?Media $mainImage): self
    {
        // TODO: Déplacer en Assert pour éviter une erreur dégueu ?!
        if (null === $mainImage->getWidth()) {
            throw new \Exception('mainImage must be an Image. Media imported is not an image');
        }

        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function issetImage()
    {
        if ($this->images->count() > 0) {
            return true;
        }

        return false;
    }

    public function addImage(Media $image): self
    {
        if (!$this->images->contains($image)) {
            if (null === $image->getWidth()) {
                throw new \Exception('Media `'.$image->getMedia().'` isn\'t an image.');
            }
            $this->images[] = $image;
        }

        return $this;
    }

    public function removeImage(Media $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }
}
