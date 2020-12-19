<?php

namespace PiedWeb\CMSBundle\Entity\PageTrait;

use Doctrine\ORM\Mapping as ORM;

trait PageParentTrait
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
}
