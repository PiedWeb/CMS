<?php

namespace PiedWeb\CMSBundle\Entity;

use PiedWeb\CMSBundle\Entity\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Page extended: // I may cut this in multiple traits
 * - meta no-index
 * - Rich Content (subtitle, excrept, parentPage, h1, name [to do short link] )
 * - Images (mainImage, images)
 * - RelatedPages
 * - author (link)
 */
trait PageTrait
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $excrept;

    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\Image")
     */
    private $mainImage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $metaIndex;

    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\Page")
     */
    private $parentPage;

    /**
     * @ORM\OneToMany(targetEntity="PiedWeb\CMSBundle\Entity\Page", mappedBy="parentPage")
     */
    private $childrenPages;

    /**
     * @ORM\ManyToMany(targetEntity="PiedWeb\CMSBundle\Entity\Image")
     */
    private $images;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $h1;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\User")
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="PiedWeb\CMSBundle\Entity\Page")
     */
    private $relatedPages;

    public function __toString()
    {
        return trim($this->name.' ');
    }

    public function __construct_extended()
    {
        $this->images = new ArrayCollection();
        $this->relatedPages = new ArrayCollection();
    }

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle): self
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    public function getExcrept(): ?string
    {
        return $this->excrept;
    }

    public function setExcrept(?string $excrept): self
    {
        $this->excrept = $excrept;

        return $this;
    }

    public function getMainImage(): ?Image
    {
        return $this->mainImage;
    }

    public function setMainImage(?Image $mainImage): self
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    public function getMetaIndex(): ?bool
    {
        return $this->metaIndex;
    }

    public function setMetaIndex(bool $metaIndex): self
    {
        $this->metaIndex = $metaIndex;

        return $this;
    }

    public function getParentPage(): ?self
    {
        return $this->parentPage;
    }

    public function setParentPage(?self $parentPage): self
    {
        $this->parentPage = $parentPage;

        return $this;
    }

    public function getChildrenPage():
    {
        return $this->childrenPages;
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

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function setH1(?string $h1): self
    {
        $this->h1 = $h1;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Page[]
     */
    public function getRelatedPages(): Collection
    {
        return $this->relatedPages;
    }

    public function addRelatedPage(Page $relatedPage): self
    {
        if (!$this->relatedPages->contains($relatedPage)) {
            $this->relatedPages[] = $relatedPage;
        }

        return $this;
    }

    public function removeRelatedPage(Page $relatedPage): self
    {
        if ($this->relatedPages->contains($relatedPage)) {
            $this->relatedPages->removeElement($relatedPage);
        }

        return $this;
    }
}
