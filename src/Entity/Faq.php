<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;

/**
 * @ORM\Entity(repositoryClass="PiedWeb\CMSBundle\Repository\FaqRepository")
 */
class Faq implements TranslatableInterface
{
    use IdTrait, FaqTrait, TranslatableTrait;
}
