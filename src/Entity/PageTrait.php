<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Cocur\Slugify\Slugify;
use Gedmo\Mapping\Annotation as Gedmo;

trait PageTrait
{
    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=150)
     */
    private $slug;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $title;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    private $mainContent;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     */
    protected $version;

    public function __toString()
    {
        return trim($this->slug.' ');
    }

    public function __construct_page()
    {
        $this->updatedAt = null !== $this->updatedAt ? $this->updatedAt : new \DateTimeImmutable();
        $this->createdAt = null !== $this->createdAt ? $this->createdAt : new \DateTimeImmutable();
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getRealSlug(): ?string
    {
        if ('homepage' == $this->slug) {
            $this->slug = '';
        }

        return $this->slug;
    }

    public function setSlug($slug): self
    {
        // work around for disabled input in sonata admin
        if (null === $slug) {
            if (null === $this->slug) {
                throw new \ErrorException('slug cant be null');
            }
        } else {
            $slg = new Slugify();
            $this->slug = $slg->slugify($slug);
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMainContent(): ?string
    {
        return $this->mainContent;
    }

    public function setMainContent(?string $mainContent): self
    {
        $this->mainContent = $mainContent;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
