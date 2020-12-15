<?php

namespace PiedWeb\CMSBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use PiedWeb\CMSBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserListener
{
    protected $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Set Password on database update if PlainPassword is set.
     */
    public function preUpdate(UserInterface $user, PreUpdateEventArgs $event)
    {
        if (\strlen($user->getPlainPassword()) > 0) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
            $user->eraseCredentials();
        }
    }
}
