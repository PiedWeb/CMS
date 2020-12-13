<?php

namespace PiedWeb\CMSBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Service\MediaCacheGeneratorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MediaCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $mediaClass;

    /**
     * @var MediaCacheGeneratorService
     */
    private $mediaCacheGenerator;

    public function __construct(
        EntityManagerInterface $em,
        MediaCacheGeneratorService $mediaCacheGenerator,
        string $mediaClass
    ) {
        $this->em = $em;
        $this->mediaClass = $mediaClass;
        $this->mediaCacheGenerator = $mediaCacheGenerator;

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
        $repo = $this->em->getRepository($this->mediaClass);

        if ($input->getArgument('media')) {
            return $repo->findBy(['media' => $input->getArgument('media')]);
        }

        return $repo->findAll();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $medias = $this->getMedias($input);

        $progressBar = new ProgressBar($output, \count($medias));
        $progressBar->start();
        foreach ($medias as $media) {
            if (false !== strpos($media->getMimeType(), 'image/')) {
                $this->mediaCacheGenerator->generateCache($media);
            }
            $progressBar->advance();
        }
        $progressBar->finish();

        return 0;
    }
}
