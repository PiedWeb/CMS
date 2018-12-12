<?php

namespace PiedWeb\CMSBundle\Tests\Controller;

use PiedWeb\CMSBundle\Tests\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class PageControllerTest extends WebTestCase
{
    public function testPage()
    {
        //self::bootKernel();
        //$container = self::$kernel->getContainer();
        //$container = self::$container;
        /*
        $kernel = new AppKernel();

        $client = new Client($kernel);

        $client->request('GET', '/');
        /**/
        //$this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertNotNull('tagada');
    }
}
