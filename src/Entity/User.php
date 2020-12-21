<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PiedWeb\CMSBundle\Entity\SharedTrait\CustomPropertiesTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface as sfUserInterface;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("email",
 *     message="user.email.already_used"
 * )
 */
class User implements UserInterface, sfUserInterface
{
    use CustomPropertiesTrait;
    use UserTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
