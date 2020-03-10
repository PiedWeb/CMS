<?php

namespace PiedWeb\CMSBundle\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait PageTrait
{
    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\NotBlank
     */
    protected $slug;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $mainContent;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     */
    protected $version;

    public function __toString()
    {
        return trim($this->slug.' ');
    }

    public function __constructPage()
    {
        $this->updatedAt = null !== $this->updatedAt ? $this->updatedAt : new \DateTime();
        $this->createdAt = null !== $this->createdAt ? $this->createdAt : new \DateTime();
    }

    public function getSlug(): ?string
    {
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
