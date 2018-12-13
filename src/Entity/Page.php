<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("slug",
 *     message="page.slug.already_used"
 * )
 */
class Page implements TranslatableInterface, PageInterface
{
    use IdTrait, PageTrait, PageExtendedTrait, PageImageTrait, TranslatableTrait;

    public function __construct()
    {
        $this->__construct_page();
        $this->__construct_extended();
        $this->__construct_image();
    }
}
