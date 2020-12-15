<?php

namespace PiedWeb\CMSBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MediaListenerTest extends KernelTestCase
{
    public function testRenameMediaOnNameUpdate()
    {
        self::bootKernel();

        $em = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $media = $em->getRepository('App\Entity\Media')->findOneBy(['media' => 'piedweb-logo.png']);
        $media->setMedia('piedweb.png');
        $em->persist($media);
        $em->flush();

        $this->assertSame(file_exists(__DIR__.'/../../Skeleton/media/piedweb.png'), true);

        $media->setMedia('piedweb-logo.png');
        $em->persist($media);
        $em->flush();
    }
}
