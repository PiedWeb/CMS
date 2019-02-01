<?php

namespace PiedWeb\CMSBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManager;
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

    public function __construct(EntityManager $em, Twig_Environment $twig, string $webDir, string $page_class)
    {
        $this->em = $em;
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
        $qb->andWhere('p.metaRobots IS NULL OR p.metaRobots NOT LIKE :noi')->setParameter('noi', '%no-index%');
        $qb->andWhere('p.mainContent NOT LIKE :noi')->setParameter('noi', 'Location:%');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
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
