<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Sonata\TranslationBundle\Model\Gedmo\AbstractPersonalTranslatable;
use Sonata\TranslationBundle\Model\Gedmo\AbstractTranslatable;

/**
 * @ORM\Entity(repositoryClass="PiedWeb\CMSBundle\Repository\FaqRepository")
 */
trait TranslatableTrait
{
    use \Sonata\TranslationBundle\Traits\Gedmo\TranslatableTrait;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    protected $locale;

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
