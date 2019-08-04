<?php

namespace PiedWeb\CMSBundle\EventListener;

use Vich\UploaderBundle\Event\Event;
use Doctrine\ORM\EntityManager;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaListener
{
    private $projectDir;
    private $iterate = 1;
    private $em;
    private $cacheManager;
    private $dataManager;
    private $filterManager;

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
        $this->checkIfFileLocationIsChanging($media);
    }

    /**
     * Todo: log i
     * If file location is changing
     * Create a redirection to the new Image.
     */
    private function checkIfFileLocationIsChanging($media)
    {
        if (null !== $media->getName() && 1 == 'todo') {
            // TODO
        }
    }

    /**
     * Si l'utilisateur ne propose pas de nom pour l'image,
     * on récupère celui d'origine duquel on enlève son extension.
     */
    private function checkIfThereIsAName($media)
    {
        if (null === $media->getName() || empty($media->getName())) {
            $media->setName(preg_replace('/\\.[^.\\s]{3,4}$/', '', $media->getMediaFile()->getClientOriginalName()));
        }
    }

    private function checkIfNameEverExistInDatabase($media)
    {
        $same = $this->em->getRepository(get_class($media))->findOneBy(['name' => $media->getName()]);
        if ($same && (null == $media->getId() || $media->getId() != $same->getId())) {
            $media->setName($media->getName().' ('.$this->iterate.')');
            ++$this->iterate;
            $this->checkIfNameEverExistInDatabase($media);
        }

        return $media;
    }

    /**
     * Update RelativeDir.
     */
    public function onVichUploaderPostUpload(Event $event)
    {
        $object = $event->getObject();
        $mapping = $event->getMapping();

        $absoluteDir = $mapping->getUploadDestination().'/'.$mapping->getUploadDir($object);
        $relativeDir = substr_replace($absoluteDir, '', 0, strlen($this->projectDir) + 1);

        $object->setRelativeDir($relativeDir);

        if (false !== strpos($object->getMimeType(), 'image/')) {
            $img = $mapping->getUploadDestination().'/'.$mapping->getUploadDir($object).'/'.$object->getMedia();
            $palette = Palette::fromFilename($img, Color::fromHexToInt('#FFFFFF'));
            $extractor = new ColorExtractor($palette);
            $colors = $extractor->extract();
            $object->setMainColor(Color::fromIntToHex($colors[0]));

            $this->generateCache('/'.$relativeDir.'/'.$object->getMedia());
        }
    }

    protected function generateCache($path)
    {
        //todo: get it from parameters (config) ?!
        foreach (['small_thumb', 'thumb', 'height_300', 'xs', 'sm', 'md', 'lg', 'xl', 'default'] as $filter) {
            $this->storeImageInCache($path, $filter).'">';
        }
    }

    protected function storeImageInCache($path, $filter)
    {
        try {
            try {
                $binary = $this->dataManager->find($filter, $path);
            } catch (NotLoadableException $e) {
                if ($defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter)) {
                    return $defaultImageUrl;
                }

                throw new NotFoundHttpException('Source image could not be found', $e);
            }

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter),
                $path,
                $filter
            );
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". '
                .'Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
        }
    }
}
