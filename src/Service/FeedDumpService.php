<?php
/*
 * This file is part of the Eko\FeedBundle Symfony bundle.
 *
 * (c) Vincent Composieux <vincent.composieux@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PiedWeb\CMSBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManager;
use PiedWeb\CMSBundle\Entity\Page;

/**
 * Inspired by https://github.com/eko/FeedBundle
 */
class FeedDumpService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var \Twig_environement
     */
    private $twig;

    /**
     * @var string
     */
    private $webDir;


    public function __construct(EntityManager $em, $twig, $webDir)
    {
        $this->em = $em;
        $this->filesystem = new Filesystem();
        $this->twig = $twig;
        $this->webDir = $webDir;
    }

    /**
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function dump()
    {
        if (!method_exists($this->filesystem, 'dumpFile')) {
            throw new \RuntimeException('Method dumpFile() is not available on your Filesystem component version, you should upgrade it.');
        }


        $dump = $this->render();
        $filepath = $this->webDir.'/feed.xml';

        $this->filesystem->dumpFile($filepath, $dump);
    }

    protected function render()
    {
        $qb = $this->em->getRepository(Page::class)->getQueryToFindPublished('p');
        $pages = $qb->getQuery()->getResult();

        return $this->twig->render('@PiedWebCMS/page/rss.xml.twig', ['pages' => $pages]);
    }
}
