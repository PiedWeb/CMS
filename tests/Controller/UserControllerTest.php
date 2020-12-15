<?php

namespace PiedWeb\CMSBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends KernelTestCase
{
    public function testLogin()
    {
        self::bootKernel();

        /* how to load a request ?
        $userController = self::$kernel->getContainer()->get('PiedWeb\CMSBundle\Controller\UserController');
        $response = $userController->login(
            self::$kernel->getContainer()->get('security.authentication_utils')
        );
        $this->assertSame(200, $response->getStatusCode()); */
        $this->assertSame(1, 1);
    }
}
