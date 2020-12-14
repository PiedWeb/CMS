<?php

namespace PiedWeb\CMSBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use PiedWeb\CMSBundle\Entity\Media;
use PiedWeb\CMSBundle\Entity\MediaExternal;

class MediaTest extends TestCase
{
    public function testBasic()
    {
        $media = new Media();
        $this->assertNull($media->getName());

        $media->setName('test');
        $this->assertSame('test', $media->getName());
    }

    public function testLoad()
    {
        // Default is the liip filter
        $src = '/media/default/test.jpg';
        $media = Media::loadFromSrc($src);

        $this->assertSame('/media', $media->getRelativeDir());
        $this->assertSame('test', $media->getSlug());

        $src = 'https://www.example.tld/media/default/test.jpg';
        $media = MediaExternal::load($src);

        $this->assertSame($src, $media->getFullPath());
    }
}
