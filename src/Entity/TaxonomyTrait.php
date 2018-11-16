<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait TaxonomyTrait
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="PiedWeb\CMSBundle\Entity\Attribute", mappedBy="parentAttribute")
     */
    private $childrenAttributes;

    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\Attribute", inversedBy="childrenAttributes")
     */
    private $parentAttribute;

    /**
     *  ORM\ManyToMany(targetEntity="PiedWeb\CMSBundle\Entity\Page", mappedBy="attributes").
     */
    //private $pages;

    public function __toString()
    {
        return $this->name.' ';
    }

    public function __construct()
    {
        $this->parentId = new ArrayCollection();
        $this->childrenAttributes = new ArrayCollection();
        $this->pages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Attribute[]
     */
    public function getChildrenAttributes(): Collection
    {
        return $this->childrenAttributes;
    }

    public function addChildrenAttribute(Attribute $childrenAttribute): self
    {
        if (!$this->childrenAttributes->contains($childrenAttribute)) {
            $this->childrenAttributes[] = $childrenAttribute;
            $childrenAttribute->setParentAttribute($this);
        }

        return $this;
    }

    public function removeChildrenAttribute(Attribute $childrenAttribute): self
    {
        if ($this->childrenAttributes->contains($childrenAttribute)) {
            $this->childrenAttributes->removeElement($childrenAttribute);
            // set the owning side to null (unless already changed)
            if ($childrenAttribute->getParentAttribute() === $this) {
                $childrenAttribute->setParentAttribute(null);
            }
        }

        return $this;
    }

    public function getParentAttribute(): ?self
    {
        return $this->parentAttribute;
    }

    public function setParentAttribute(?self $parentAttribute): self
    {
        $this->parentAttribute = $parentAttribute;

        return $this;
    }

    /*
     * @return Collection|Page[]
     *
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->addAttribute($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->contains($page)) {
            $this->pages->removeElement($page);
            $page->removeAttribute($this);
        }

        return $this;
    }*/
}
