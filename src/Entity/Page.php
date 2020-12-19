<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Entity\PageTrait\PageExtendedTrait;
use PiedWeb\CMSBundle\Entity\PageTrait\PageI18nTrait;
use PiedWeb\CMSBundle\Entity\PageTrait\PageImageTrait;
use PiedWeb\CMSBundle\Entity\PageTrait\PageMultiHostTrait;
use PiedWeb\CMSBundle\Entity\PageTrait\PageRedirectionTrait;
use PiedWeb\CMSBundle\Entity\PageTrait\PageTrait;
use PiedWeb\CMSBundle\Entity\SharedTrait\CustomPropertiesTrait;
use PiedWeb\CMSBundle\Entity\SharedTrait\IdTrait;
use PiedWeb\CMSBundle\Validator\Constraints\PageRendering;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(
 *     fields={"host", "slug"},
 *     errorPath="slug",
 *     message="page.slug.already_used"
 * )
 * @PageRendering
 */
class Page implements PageInterface
{
    use CustomPropertiesTrait;
    use IdTrait;
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
