<?php

namespace PiedWeb\CMSBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PageRepositoryTest extends KernelTestCase
{
    public function testPageRepo()
    {
        self::bootKernel();

        $em = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $pages = $em->getRepository('App\Entity\Page')->getIndexablePages(null, true, 'en', 'en', 1)
            ->getQuery()->getResult();

        $this->assertSame($pages[0]->getSlug(), 'homepage');
    }
}
