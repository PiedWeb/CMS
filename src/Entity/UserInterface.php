<?php

namespace PiedWeb\CMSBundle\Entity;

interface UserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';

    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function getPlainPassword();

    public function setPassword(string $password);

    public function getSalt();

    public function eraseCredentials();
}
