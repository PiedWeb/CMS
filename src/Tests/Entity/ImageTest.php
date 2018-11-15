<?php

namespace PiedWeb\CMSBundle\Tests\Entity;

use PiedWeb\CMSBundle\Entity\Media;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testMedia()
    {
        $Media = new Media();
        $this->assertNull($Media->getName());

        $Media->setName('test');
        $this->assertSame('test', $Media->getName());
    }
}
