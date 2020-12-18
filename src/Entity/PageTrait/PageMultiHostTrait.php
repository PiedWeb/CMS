<?php

namespace PiedWeb\CMSBundle\Entity\PageTrait;

use Doctrine\ORM\Mapping as ORM;

trait PageMultiHostTrait
{
    /**
     * @ORM\Column(type="string", length=253, nullable=true)
     */
    protected $host;

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost($host): self
    {
        $this->host = $host;

        return $this;
    }
}
