<?php

namespace PiedWeb\CMSBundle\Tests\Entity;

use PiedWeb\CMSBundle\Entity\Page;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    public function testTitle()
    {
        $page = new Page();
        $this->assertNull($page->getTitle());

        $page->setTitle('hello');
        $this->assertSame('hello', $page->getTitle());
    }
}
