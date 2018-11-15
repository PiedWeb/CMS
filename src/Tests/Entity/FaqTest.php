<?php

namespace PiedWeb\CMSBundle\Tests\Entity;

use PiedWeb\CMSBundle\Entity\Faq;
use PHPUnit\Framework\TestCase;

class FaqTest extends TestCase
{
    public function testQuestion()
    {
        $faq = new Faq();
        $this->assertNull($faq->getQuestion());

        $faq->setQuestion('Alo ?');
        $this->assertSame('Alo ?', $faq->getQuestion());
    }
}
