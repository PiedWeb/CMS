<?php

namespace PiedWeb\CMSBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

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
