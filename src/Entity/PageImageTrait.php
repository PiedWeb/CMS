<?php

namespace PiedWeb\CMSBundle\Entity;

use PiedWeb\CMSBundle\Entity\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait PageTrait
{

    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\Image")
     */
    private $mainImage;

    /**
     * @ORM\ManyToMany(targetEntity="PiedWeb\CMSBundle\Entity\Image")
     */
    private $images;

    public function getMainImage(): ?Image
    {
        return $this->mainImage;
    }

    public function setMainImage(?Image $mainImage): self
    {
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

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }
}
