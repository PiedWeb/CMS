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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $h1;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\UserInterface")
     */
    protected $author;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    protected $template;

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

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function getH1eTitle(): ?string
    {
        return $this->h1 ?? $this->title;
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

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }
}
