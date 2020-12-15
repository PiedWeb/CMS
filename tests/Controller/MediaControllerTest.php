<?php

namespace PiedWeb\CMSBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class MediaControllerTest extends KernelTestCase
{
    public function testDownload()
    {
        $slug = 'homepage';
        $response = $this->getMediaController()->download('media/piedweb-logo.png');
        $this->assertTrue(200 === $response->getStatusCode());
    }

    public function getMediaController()
    {
        return $this->getService('PiedWeb\CMSBundle\Controller\MediaController');
    }

    public function getService(string $service)
    {
        self::bootKernel();

        return self::$kernel->getContainer()->get($service);
    }
}
