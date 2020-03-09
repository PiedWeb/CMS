<?php

namespace PiedWeb\CMSBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig_Environment;

/**
 * Inspired by https://github.com/eko/FeedBundle.
 */
class FeedDumpService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var \PiedWeb\CMSBundle\Service\PageCanonicalService
     */
    private $pc;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @var string
     */
    private $page_class;

    public function __construct(
        EntityManager $em,
        Twig_Environment $twig,
        PageCanonicalService $pc,
        string $webDir,
        string $page_class
    ) {
        $this->em = $em;
        $this->pc = $pc;
        $this->filesystem = new Filesystem();
        $this->twig = $twig;
        $this->webDir = $webDir;
        $this->page_class = $page_class;
    }

    /**
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function dump()
    {
        $this->dumpFeed();
        $this->dumpSitemap();
    }

    public function postUpdate()
    {
        $this->dump();
    }

    protected function dumpFeed()
    {
        $dump = $this->renderFeed();
        $filepath = $this->webDir.'/feed.xml';

        $this->filesystem->dumpFile($filepath, $dump);
    }

    protected function dumpSitemap()
    {
        $pages = $this->getPages();
        $this->filesystem->dumpFile($this->webDir.'/sitemap.txt', $this->renderSitemapTxt($pages));
        $this->filesystem->dumpFile($this->webDir.'/sitemap.xml', $this->renderSitemapXml($pages));
    }

    protected function getPages(?int $limit = null)
    {
        $qb = $this->em->getRepository($this->page_class)->getQueryToFindPublished('p');
        $qb->andWhere('p.metaRobots IS NULL OR p.metaRobots NOT LIKE :noi2')
           ->setParameter('noi2', '%noindex%');
        $qb->andWhere('p.mainContent NOT LIKE :noi')->setParameter('noi', 'Location:%');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        $pages = $qb->getQuery()->getResult();

        //foreach ($pages as $page) echo $page->getMetaRobots().' '.$page->getTitle().'<br>';
        //exit('feed updated');

        return $pages;
    }

    protected function renderFeed()
    {
        return $this->twig->render('@PiedWebCMS/page/rss.xml.twig', ['pages' => $this->getPages(5)]);
    }

    protected function renderSitemapTxt($pages)
    {
        return $this->twig->render('@PiedWebCMS/page/sitemap.txt.twig', ['pages' => $pages]);
    }

    protected function renderSitemapXml($pages)
    {
        return $this->twig->render('@PiedWebCMS/page/sitemap.xml.twig', ['pages' => $pages]);
    }
}
