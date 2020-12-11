<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Page extended: // I may cut this in multiple traits
 * - meta no-index
 * - Rich Content (meta desc, parentPage, h1, name [to do short link] )
 * - author (link)
 * - template.
 */
trait PageExtendedTrait
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $excrept;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $metaRobots;

    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\PageInterface", inversedBy="childrenPages")
     */
    protected $parentPage;

    /**
     * @ORM\OneToMany(targetEntity="PiedWeb\CMSBundle\Entity\PageInterface", mappedBy="parentPage")
     * @ORM\OrderBy({"id"                                                    = "ASC"})
     */
    protected $childrenPages;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $title;

    public function getTitle($elseReturnH1 = false): ?string
    {
        return $this->title ?? (true === $elseReturnH1 ? $this->h1 : null);
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function __constructExtended()
    {
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

    public function getMetaRobots(): ?string
    {
        return $this->metaRobots;
    }

    public function setMetaRobots(?string $metaRobots): self
    {
        $this->metaRobots = $metaRobots;

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

    public function getChildrenPages()
    {
        return $this->childrenPages;
    }

    public function getName($firstOnly = false): ?string
    {
        if ($firstOnly) {
            return trim(explode(',', $this->name)[0]) ?? $this->name ?? $this->h1 ?? $this->title;
        }

        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
