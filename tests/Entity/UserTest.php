<?php

namespace PiedWeb\CMSBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use PiedWeb\CMSBundle\Entity\User;

class UserTest extends TestCase
{
    public function testBasics()
    {
        $user = new User();
        $this->assertNull($user->getEmail());

        $user->setEmail('test@example.tld');
        $this->assertSame('test@example.tld', (string) $user);
    }
}
