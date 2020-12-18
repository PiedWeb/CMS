<?php

namespace PiedWeb\CMSBundle\Entity\PageTrait;

use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Extension\Filter\FilterInterface;
use Exception;

trait PageExtendedTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\PageInterface", inversedBy="childrenPages")
     */
    protected $parentPage;

    /**
     * @ORM\OneToMany(targetEntity="PiedWeb\CMSBundle\Entity\PageInterface", mappedBy="parentPage")
     * @ORM\OrderBy({"id": "ASC"})
     */
    protected $childrenPages;

    /**
     * Meta Description #SEO
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $searchExcrept;

    /**
     * #SEO
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $metaRobots;


    /**
     * #SEO (links) / Breadcrumb
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $title;


    /** @var FilterInterface */
    protected $content;

    public function getContent()
    {
        return $this->content;
    }

    public function setContent(FilterInterface $content)
    {
        $this->content = $content;

        return $this;
    }

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

    public function getExcrept(): ?string
    {
        return $this->searchExcrept;
    }

    public function setExcrept(?string $searchExcrept): self
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
