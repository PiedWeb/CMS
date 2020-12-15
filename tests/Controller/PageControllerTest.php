<?php

namespace PiedWeb\CMSBundle\Tests\Controller;

use PiedWeb\CMSBundle\Controller\PageController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class PageControllerTest extends KernelTestCase
{
    public function testShowHomepage()
    {
        $slug = 'homepage';
        $response = $this->getPageController()->show($slug, 'localhost.dev', Request::create($slug));
        $this->assertTrue(200 === $response->getStatusCode());
    }

    public function testShowPreview()
    {
        $slug = 'homepage';
        $response = $this->getPageController()->preview($slug, 'localhost.dev', Request::create($slug));

        $this->assertTrue(200 === $response->getStatusCode());
    }


    public function testShowMainFeed()
    {
        $response = $this->getPageController()->showMainFeed('localhost.dev', Request::create('/feed.xml'));

        $this->assertTrue(200 === $response->getStatusCode());
    }

    public function testShowSitemap()
    {
        $response = $this->getPageController()->showSitemap('xml', 'localhost.dev', Request::create('/sitemap.xml'));

        $this->assertTrue(200 === $response->getStatusCode());
    }

    public function getPageController()
    {
        return $this->getService('PiedWeb\CMSBundle\Controller\PageController');
    }

    public function getService(string $service)
    {
        self::bootKernel();

        return self::$kernel->getContainer()->get($service);
    }
}
