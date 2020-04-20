<?php

namespace PiedWeb\CMSBundle\EventListener;

use Doctrine\ORM\EntityManager;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Vich\UploaderBundle\Event\Event;

class MediaListener
{
    use MediaCacheGeneratorTrait;

    protected $projectDir;
    protected $iterate = 1;
    protected $em;
    protected $cacheManager;
    protected $dataManager;
    protected $filterManager;

    public function __construct(
        string $projectDir,
        EntityManager $em,
        CacheManager $cacheManager,
        DataManager $dataManager,
        FilterManager $filterManager
    ) {
        $this->projectDir = $projectDir;
        $this->em = $em;
        $this->cacheManager = $cacheManager;
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
    }

    /**
     * Check if name exist.
     */
    public function onVichUploaderPreUpload(Event $event)
    {
        $media = $event->getObject();

        $this->checkIfThereIsAName($media);
        $this->checkIfNameEverExistInDatabase($media);
    }

    /**
     * Si l'utilisateur ne propose pas de nom pour l'image,
     * on récupère celui d'origine duquel on enlève son extension.
     */
    protected function checkIfThereIsAName($media)
    {
        if (null === $media->getName() || empty($media->getName())) {
            $media->setName(preg_replace('/\\.[^.\\s]{3,4}$/', '', $media->getMediaFile()->getClientOriginalName()));
        }
    }

    protected function checkIfNameEverExistInDatabase($media)
    {
        $same = $this->em->getRepository(get_class($media))->findOneBy(['name' => $media->getName()]);
        if ($same && (null == $media->getId() || $media->getId() != $same->getId())) {
            $media->setName(preg_replace('/\([0-9]+\)$/', '', $media->getName()).' ('.$this->iterate.')');
            ++$this->iterate;
            $this->checkIfNameEverExistInDatabase($media);
        }
    }

    /**
     * Update RelativeDir.
     */
    public function onVichUploaderPostUpload(Event $event)
    {
        $media = $event->getObject();
        $mapping = $event->getMapping();

        $absoluteDir = $mapping->getUploadDestination().'/'.$mapping->getUploadDir($media);
        $relativeDir = substr_replace($absoluteDir, '', 0, strlen($this->projectDir) + 1);

        $media->setRelativeDir($relativeDir);

        if (false !== strpos($media->getMimeType(), 'image/')) {
            $img = $mapping->getUploadDestination().'/'.$mapping->getUploadDir($media).'/'.$media->getMedia();
            $palette = Palette::fromFilename($img, Color::fromHexToInt('#FFFFFF'));
            $extractor = new ColorExtractor($palette);
            $colors = $extractor->extract();
            $media->setMainColor(Color::fromIntToHex($colors[0]));

            $this->generateCache($media);
        }
    }
}
