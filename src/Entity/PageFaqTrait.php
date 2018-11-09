<?php

namespace PiedWeb\CMSBundle\Entity;

use PiedWeb\CMSBundle\Entity\Faq;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait PageFaqTrait
{
    /**
     * @ORM\ManyToMany(targetEntity="PiedWeb\CMSBundle\Entity\Faq")
     */
    private $faq;

    public function __construct_faq()
    {
        $this->faq = new ArrayCollection();
    }

    public function getFaq(): Collection
    {
        return $this->faq;
    }

    public function addFaq(Faq $faq): self
    {
        if (!$this->faq->contains($faq)) {
            $this->faq[] = $faq;
        }

        return $this;
    }

    public function removeFaq(Faq $faq): self
    {
        if ($this->faq->contains($faq)) {
            $this->faq->removeElement($faq);
        }

        return $this;
    }

    public function issetFaq(): bool
    {
        if ($this->faq->count() > 0) {
            return true;
        }

        return false;
    }
}
