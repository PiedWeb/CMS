<?php

namespace PiedWeb\CMSBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use PiedWeb\CMSBundle\Entity\Page;

class PageTest extends TestCase
{
    public function testBasics()
    {
        $page = new Page();
        $this->assertNull($page->getTitle());

        $page->setTitle('hello');
        $this->assertSame('hello', $page->getTitle());

        $page->setSlug('hello you');
        $this->assertSame('hello-you', $page->getSlug());
    }
}
