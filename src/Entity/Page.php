<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PiedWeb\CMSBundle\Repository\PageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Page
{
    use PageTrait, use PageExtendedTrait, use PageImageTrait, PageFaqTrait;

    private function __construct() {
        $this->__construct_page();
        $this->__construct_extended();
        $this->__construct_image();
        $this->__construct_faq();
    }
}
