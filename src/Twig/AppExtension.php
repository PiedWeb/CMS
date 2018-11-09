<?php
namespace PiedWeb\CMSBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('imgResponsive', array($this, 'imgResponsive')),
        );
    }

    public function imgResponsive($image)
    {
        // Trouver le dernier point
        // Le remplacer par -size.

        return $image;
    }
}
