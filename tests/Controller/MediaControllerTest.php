<?php

namespace PiedWeb\CMSBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MediaControllerTest extends KernelTestCase
{
    public function testDownload()
    {
        self::bootKernel();

        $mediaController = self::$kernel->getContainer()->get('PiedWeb\CMSBundle\Controller\MediaController');
        $response = $mediaController->download('media/piedweb-logo.png');
        $this->assertTrue(200 === $response->getStatusCode());
    }
}
