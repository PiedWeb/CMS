<?php

namespace PiedWeb\CMSBundle\Tests\Entity;

use PiedWeb\CMSBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testEmail()
    {
        $user = new User();
        $this->assertNull($user->getEmail());

        $user->setEmail('contact@piedweb.com');
        $this->assertSame('contact@piedweb.com', $user->getEmail());
    }
}
