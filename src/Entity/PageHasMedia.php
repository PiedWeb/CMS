<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class PageHasMedia
{
    use IdTrait;

    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\MediaInterface")
     */
    protected $media;

    /**
     * @ORM\ManyToOne(targetEntity="PiedWeb\CMSBundle\Entity\PageInterface", inversedBy="pageHasMedias")
     */
    protected $page;

    /**
     * @ORM\Column(type="integer")
     */
    protected $position = 0;

    public function __toString()
    {
        return $this->getPage().' | '.$this->getMedia();
    }

    public function setPage(?PageInterface $page = null)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setMedia(MediaInterface $media = null)
    {
        $this->media = $media;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }
}
