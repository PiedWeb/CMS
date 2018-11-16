<?php

// src/AppBundle/Entity/User.php

namespace PiedWeb\CMSBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("email",
 *     message="user.email.already_used"
 * )
 */
class User extends BaseUser
{
    use UserTrait, UserExtendedTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
