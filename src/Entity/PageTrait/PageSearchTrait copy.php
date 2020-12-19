<?php

namespace PiedWeb\CMSBundle\Entity\PageTrait;

use Doctrine\ORM\Mapping as ORM;
use Exception;

trait PageSearchTraitCopy
{
    /**
     * Meta Description -SEO.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $searchExcrept;

    /**
     * - SEO.
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $metaRobots;

    /**
     * - SEO (links) / Breadcrumb.
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $name;

    /**
     * HTML Title - SEO.
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $title;

    public function getTemplate(): ?string
    {
        return $this->getCustomProperty('template');
    }

    public function getReadableContent()
    {
        throw new Exception('You should use getContent.content');
    }

    public function getChapeau()
    {
        throw new Exception('You should use getContent');
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

    public function __constructExtended()
    {
    }

    public function getSearchExcrept(): ?string
    {
        return $this->searchExcrept;
    }

    public function setSearchExcrept(?string $searchExcrept): self
    {
        $this->searchExcrept = $searchExcrept;

        return $this;
    }

    public function getMetaRobots(): ?string
    {
        return $this->metaRobots;
    }

    public function setMetaRobots(?string $metaRobots): self
    {
        $this->metaRobots = $metaRobots;

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
}
