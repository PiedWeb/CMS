<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;

/**
 * @ORM\Entity(repositoryClass="PiedWeb\CMSBundle\Repository\FaqRepository")
 */
class Faq implements TranslatableInterface
{
    use IdTrait, FaqTrait, TranslatableTrait;
}
