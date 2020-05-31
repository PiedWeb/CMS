<?php

namespace PiedWeb\CMSBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Repository\PageRepository;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment as Twig;

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

    /** @var Twig */
    private $twig;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @var string
     */
    private $page_class;

    private $locale;
    private $locales;

    public function __construct(
        EntityManager $em,
        Twig $twig,
        PageCanonicalService $pc,
        string $webDir,
        string $page_class,
        string $locale,
        string $locales
    ) {
        $this->em = $em;
        $this->pc = $pc;
        $this->filesystem = new Filesystem();
        $this->twig = $twig;
        $this->webDir = $webDir;
        $this->page_class = $page_class;
        $this->locale = $locale;
        $this->locales = explode('|', $locales);
        if (empty($this->locales)) {
            $this->locales = [$locale];
        }
    }

    /**
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function dump()
    {
        $this->dumpSitemap();

        foreach ($this->locales as $locale) {
            $this->dumpFeed($locale);
            $this->dumpSitemap($locale);
        }
    }

    public function postUpdate()
    {
        $this->dump();
    }

    protected function dumpFeed(string $locale)
    {
        $dump = $this->renderFeed($locale, 'feed'.($this->locale == $locale ? '' : '.'.$locale).'.xml');
        $filepath = $this->webDir.'/feed'.($this->locale == $locale ? '' : '.'.$locale).'.xml';

        $this->filesystem->dumpFile($filepath, $dump);
    }

    protected function dumpSitemap(?string $locale = null)
    {
        //$file = $this->webDir.'/sitemap'.($this->locale == $locale ? '' : '.'.$locale);
        $file = $this->webDir.'/sitemap'.(null === $locale ? '' : '.'.$locale);

        $pages = $this->getPages($locale);
        $this->filesystem->dumpFile($file.'.txt', $this->renderSitemapTxt($pages));
        $this->filesystem->dumpFile($file.'.xml', $this->renderSitemapXml($pages));
    }

    protected function getPageRepository(): PageRepository
    {
        return $this->em->getRepository($this->page_class);
    }

    protected function getPages(?string $locale, ?int $limit = null)
    {
        $qb = $this->getPageRepository()->getQueryToFindPublished('p');
        $qb->andWhere('p.metaRobots IS NULL OR p.metaRobots NOT LIKE :noi2')
            ->setParameter('noi2', '%noindex%');
        $qb->andWhere('p.mainContent IS NULL OR p.mainContent NOT LIKE :noi')->setParameter('noi', 'Location:%');

        // avoid bc break and site with no locale configured
        if (null !== $locale) {
            $qb->andWhere(($this->locale == $locale ? 'p.locale IS NULL OR ' : '').'p.locale LIKE :locale')
            ->setParameter('locale', $locale);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        $pages = $qb->getQuery()->getResult();

        //foreach ($pages as $page) echo $page->getMetaRobots().' '.$page->getTitle().'<br>';
        //exit('feed updated');

        return $pages;
    }

    protected function renderFeed(string $locale, string $feedUri)
    {
        // assuming yoy name it with your locale identifier
        $LocaleHomepage = $this->em->getRepository($this->page_class)->findOneBy(['slug' => $locale]);
        $page = $LocaleHomepage ?? $this->em->getRepository($this->page_class)->findOneBy(['slug' => 'homepage']);

        return $this->twig->render('@PiedWebCMS/page/rss.xml.twig', [
            'pages' => $this->getPages($locale, 5),
            'page' => $page,
            'feedUri' => $feedUri,
        ]);
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
