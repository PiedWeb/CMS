<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Validator\Constraints\PageRendering;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("slug",
 *     message="page.slug.already_used"
 * )
 * @PageRendering()
 */
class Page implements TranslatableInterface, PageInterface
{
    use IdTrait;
    use PageTrait;
    use PageExtendedTrait;
    use PageImageTrait;
    use PageExtendedMainContentTrait;
    use PageRedirectionTrait;
    use TranslatableTrait;

    public function __construct()
    {
        $this->__constructPage();
        $this->__constructExtended();
        $this->__constructImage();
    }

    //abstract public function getTemplate();
}
