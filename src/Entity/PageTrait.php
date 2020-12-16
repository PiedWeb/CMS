<?php

namespace PiedWeb\CMSBundle\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Extension\PageMainContentManager\ShortCodeConverter;

trait PageTrait
{
    /**
     * @ORM\Column(type="string", length=150)
     */
    protected $slug;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $h1;

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function setH1(?string $h1): self
    {
        $this->h1 = $h1;

        return $this;
    }

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $mainContent;

    /**
     * In fact, createdAt is more a publishedAt.
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    public function __toString()
    {
        return trim($this->host.'/'.$this->slug.' ');
    }

    public function __constructPage()
    {
        $this->updatedAt = null !== $this->updatedAt ? $this->updatedAt : new \DateTime();
        $this->createdAt = null !== $this->createdAt ? $this->createdAt : new \DateTime();
        $this->slug = '';
    }

    public function getSlug(): ?string
    {
        if (! $this->slug) {
            return $this->id;
        }

        return $this->slug;
    }

    public function getRealSlug(): ?string
    {
        if ('homepage' == $this->slug) {
            return '';
        }

        return $this->slug;
    }

    public function setSlug($slug, $set = false): self
    {
        if (true === $set) {
            $this->slug = $slug;
        } elseif (null === $slug) { // work around for disabled input in sonata admin
            if (null === $this->slug) {
                throw new \ErrorException('slug cant be null');
            }
        } else {
            $slg = new Slugify(['regexp' => '/[^A-Za-z0-9\/]+/']);
            $slug = $slg->slugify($slug);
            $slug = trim($slug, '/');
            $this->slug = $slug; //$this->setSlug(trim($slug, '/'), true);
        }

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
