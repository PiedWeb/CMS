<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait PageRelatedPagesTrait
{
    /**
     * @ORM\ManyToMany(targetEntity="PiedWeb\CMSBundle\Entity\PageInterface")
     */
    protected $relatedPages;

    public function __constructRelatedPagesT()
    {
        $this->relatedPages = new ArrayCollection();
    }

    /**
     * @return Collection|Page[]
     */
    public function getRelatedPages(): Collection
    {
        return $this->relatedPages;
    }

    public function addRelatedPage(PageInterface $relatedPage): self
    {
        if (! $this->relatedPages->contains($relatedPage)) {
            $this->relatedPages[] = $relatedPage;
        }

        return $this;
    }

    public function removeRelatedPage(PageInterface $relatedPage): self
    {
        if ($this->relatedPages->contains($relatedPage)) {
            $this->relatedPages->removeElement($relatedPage);
        }

        return $this;
    }
}
