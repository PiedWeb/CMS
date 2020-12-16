<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Entity\Page as BasePage;
use PiedWeb\CMSBundle\Repository\PageRepository;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 */
class Page extends BasePage
{
}
