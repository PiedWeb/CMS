<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Validator\Constraints\PageRendering;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields={"host", "slug"},
 *     errorPath="slug",
 *     message="page.slug.already_used"
 * )
 * @PageRendering()
 */
class Page implements PageInterface
{
    use IdTrait;
    use PageExtendedMainContentTrait;
    use PageExtendedTrait;
    use PageI18nTrait;
    use PageImageTrait;
    use PageMultiHostTrait;
    use PageRedirectionTrait;
    use PageTrait;

    public function __construct()
    {
        $this->__constructPage();
        $this->__constructExtended();
        $this->__constructImage();
        $this->__constructI18n();
    }
}
