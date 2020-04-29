<?php

namespace PiedWeb\CMSBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PiedWeb\CMSBundle\EventListener\MediaCacheGeneratorTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MediaCommand extends Command
{
    use MediaCacheGeneratorTrait;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @var string
     */
    private $staticDir;

    private $params;

    protected $redirections = '';

    public function __construct(
        EntityManagerInterface $em,
        ParameterBagInterface $params,
        CacheManager $cacheManager,
        DataManager $dataManager,
        FilterManager $filterManager,
        string $webDir
    ) {
        $this->em = $em;
        $this->params = $params;
        $this->webDir = $webDir;
        $this->cacheManager = $cacheManager;
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->staticDir = $this->webDir.'/../static';
        $this->projectDir = $this->webDir.'/..';

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('media:cache:generate')
            ->setDescription('Generate all images cache')
            ->addArgument('media', InputArgument::OPTIONAL, 'Image path (without `/media/`) to (re)generate cache.');
    }

    protected function getMedias(InputInterface $input)
    {
        $repo = $this->em->getRepository($this->params->get('app.entity_media'));

        if ($input->getArgument('media')) {
            return $repo->findByMedia($input->getArgument('media'));
        }

        return $repo->findAll();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $medias = $this->getMedias($input);

        $progressBar = new ProgressBar($output, count($medias));
        $progressBar->start();
        foreach ($medias as $media) {
            if (false !== strpos($media->getMimeType(), 'image/')) {
                $this->generateCache($media);
            }
            $progressBar->advance();
        }
        $progressBar->finish();
    }
}
