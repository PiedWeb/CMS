<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Entity\Page as BasePage;
use PiedWeb\CMSBundle\Repository\PageRepositoryInterface;

/**
 * @ORM\Entity(repositoryClass=PageRepositoryInterface::class)
 */
class Page extends BasePage
{
}
