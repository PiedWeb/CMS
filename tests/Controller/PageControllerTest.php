<?php

namespace PiedWeb\CMSBundle\Tests\Controller;

use PiedWeb\CMSBundle\Controller\PageController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class PageControllerTest extends KernelTestCase
{
    public function testShowHomepage()
    {
        $controller = $this->getService('PiedWeb\CMSBundle\Controller\PageController');
        //$controller = new PageController();
        $response = $controller->show('homepage', 'localhost.dev', Request::create('/homepage'));

        $this->assertTrue(200 === $response->getStatusCode());
    }

    public function getService(string $service)
    {
        self::bootKernel();

        return self::$kernel->getContainer()->get($service);
    }
}
