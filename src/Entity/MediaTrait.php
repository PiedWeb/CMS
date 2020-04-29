<?php

namespace PiedWeb\CMSBundle\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util\Urlizer;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

trait MediaTrait
{
    use TimestampableEntity;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $mimeType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $relativeDir;

    /**
     * @ORM\Column(type="integer")
     */
    protected $size;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $height;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $width;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $mainColor;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $media;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(
     *     mapping="media_media",
     *     fileNameProperty="slug",
     *     mimeType="mimeType",
     *     size="size",
     *     dimensions="dimensions"
     * )
     *
     * @var File
     */
    protected $mediaFile;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $names;

    protected $slug;

    /**
     * @ORM\OneToMany(
     *     targetEntity="PiedWeb\CMSBundle\Entity\PageHasMediaInterface",
     *     mappedBy="media",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $pageHasMedias;

    /**
     * @ORM\OneToMany(
     *      targetEntity="PiedWeb\CMSBundle\Entity\PageInterface",
     *      mappedBy="mainImage"
     * )
     */
    protected $mainImagePages;

    public function __toString()
    {
        return $this->name.' ';
    }

    public function setSlug($slug, $force = false)
    {
        if (true === $force) {
            $this->slug = $slug;

            return $this;
        }

        $this->setMedia($slug);

        return $this;
    }

    public function getSlug(): string
    {
        if ($this->slug) {
            return $this->slug;
        }

        if ($this->media) {
            return $this->slug = preg_replace('/\\.[^.\\s]{3,4}$/', '', $this->media);
        }

        $slugify = new Slugify();
        $this->slug = $slugify->slugify($this->getName()); //Urlizer::urlize($this->getName());

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

    public function getName($getLocalized = null, $onlyLocalized = false): ?string
    {
        $names = $this->getNames(true);

        return $getLocalized ?
            (isset($names[$getLocalized]) ? $names[$getLocalized] : ($onlyLocalized ? null : $this->name))
            : $this->name;
    }

    public function getNames($yaml = false)
    {
        return true === $yaml && null !== $this->names ? Yaml::parse($this->names) : $this->names;
    }

    public function setNames(?string $names): self
    {
        $this->name = $names;

        return $this;
    }

    public function getNameByLocale($locale)
    {
        $names = $this->getNames(true);

        return $names[$locale] ?? $this->getName();
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRelativeDir(): ?string
    {
        return rtrim($this->relativeDir, '/');
    }

    public function setRelativeDir($relativeDir): self
    {
        $this->relativeDir = rtrim($relativeDir, '/');

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

    public function getRatio(): float
    {
        return $this->height / $this->width;
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
        $pageHasMedia->setMedia($this);
        $this->pageHasMedias[] = $pageHasMedia;
    }

    public function removePageHasMedia(PageHasMedia $pageHasMedia)
    {
        $this->pageHasMedias->removeElement($pageHasMedia);
    }

    public function getMainImagePages()
    {
        return $this->mainImagePages;
    }

    /**
     * @ORM\PreRemove
     */
    public function removeMainImageFromPages()
    {
        foreach ($this->mainImagePages as $page) {
            $page->setMainImage(null);
        }
    }

    public function getPath(): ?string
    {
        return $this->getFullPath();

    }

    public function getFullPath(): ?string
    {
        return null !== $this->media
            ? '/'.$this->getRelativeDir().($this->getMedia() ? '/'.$this->getMedia() : '')
            : null;
    }

    public function getFullPathWebP(): ?string
    {
        return null !== $this->media
            ? '/'.$this->getRelativeDir().($this->getSlug() ? '/'.$this->getSlug().'.webp' : '')
            : null;
    }
}
