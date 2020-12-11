<?php

namespace PiedWeb\CMSBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use PiedWeb\CMSBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserListener
{
    protected $passwordEncoder;

    public function __construct(UserPasswordEncoder $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function preUpdate(UserInterface $user, PreUpdateEventArgs $event)
    {
        if (strlen($user->getPlainPassword()) > 0) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
            $user->eraseCredentials();
        }
    }
}
