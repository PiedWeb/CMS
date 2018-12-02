<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Gedmo\Sluggable\Util\Urlizer;
use Cocur\Slugify\Slugify;
use Gedmo\Mapping\Annotation as Gedmo;

trait MediaTrait
{
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $mimeType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $relativeDir;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $mainColor;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $media;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="media_media", fileNameProperty="media", mimeType="mimeType", size="size", dimensions="dimensions")
     *
     * @var File
     */
    private $mediaFile;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $name;

    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="PiedWeb\CMSBundle\Entity\PageHasMedia", mappedBy="media",cascade={"all"}, orphanRemoval=true)
     */
    private $pageHasMedias;

    public function __toString()
    {
        return $this->name.' ';
    }

    public function getSlug(): string
    {
        if (!$this->slug) {
            $slugify = new Slugify();

            return $this->slug = $slugify->slugify($this->getName()); //Urlizer::urlize($this->getName());
        }

        return $this->slug;
    }

    public function setMediaFile(?File $media = null): void
    {
        $this->mediaFile = $media;

        if (null !== $media) { //normaly no more need with gedmo traits
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getMediaFile(): ?File
    {
        return $this->mediaFile;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia($media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        //if ($this->name !== null) { // sinon renommer l'media
        //throw new \Exception('Can\'t edit name.');
        //}

        $this->name = $name;

        return $this;
    }

    public function getRelativeDir(): ?string
    {
        return $this->relativeDir;
    }

    public function setRelativeDir($relativeDir): self
    {
        $this->relativeDir = $relativeDir;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType($mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size): self
    {
        $this->size = $size;

        return $this;
    }

    public function setDimensions($dimensions): self
    {
        if (isset($dimensions[0])) {
            $this->width = (int) $dimensions[0];
        }

        if (isset($dimensions[1])) {
            $this->height = (int) $dimensions[1];
        }

        return $this;
    }

    public function getDimensions(): array
    {
        return [$this->width, $this->height];
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getMainColor(): ?string
    {
        return $this->mainColor;
    }

    public function setMainColor(?string $mainColor): self
    {
        $this->mainColor = $mainColor;

        return $this;
    }


    public function setPageHasMedias($pageHasMedias)
    {
        $this->pageHasMedias = new ArrayCollection();
        foreach ($pageHasMedias as $pageHasMedia) {
            $this->addPageHasMedia($pageHasMedia);
        }
    }

    public function getPageHasMedias()
    {
        return $this->pageHasMedias;
    }

    public function addPageHasMedia(PageHasMedia $pageHasMedia)
    {
        $pageHasMedia->setPage($this);
        $this->pageHasMedias[] = $pageHasMedia;
    }

    public function removePageHasMedia(PageHasMedia $pageHasMedia)
    {
        $this->pageHasMedias->removeElement($pageHasMedia);
    }
}
