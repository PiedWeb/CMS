<?php

namespace PiedWeb\CMSBundle\EventListener;

use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PiedWeb\CMSBundle\Entity\MediaInterface;
use PiedWeb\CMSBundle\Service\WebPConverter;
//use WebPConvert\Convert\Converters\Stack as WebPConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @require dataManager
 * cacheManager
 * filterManager
 */
trait MediaCacheGeneratorTrait
{
    protected $projectDir;

    protected $webPConverterOptions = [
        'converters' => ['cwebp', 'gd', 'vips', 'imagick', 'gmagick', 'imagemagick', 'graphicsmagick', 'wpc', 'ewww'],
    ];

    protected function generateCache(MediaInterface $media)
    {
        $this->createWebP($media);

        $path = '/'.$media->getRelativeDir().'/'.$media->getMedia();
        $binary = $this->getBinary($path);
        //$pathWebP = '/'.$media->getRelativeDir().'/'.$media->getSlug().'.webp';

        //todo: get liip conf from parameters (config) ?!
        foreach (['small_thumb', 'thumb', 'height_300', 'xs', 'sm', 'md', 'lg', 'xl', 'default'] as $filter) {
            $this->storeImageInCache($path, $binary, $filter);
            $this->imgToWebP($media, $filter);
            //$this->storeImageInCache($pathWebP, $binary, $filter); liip not optimized...
        }
    }

    protected function getBinary($path)
    {
        try {
            $binary = $this->dataManager->find('default', $path);
        } catch (NotLoadableException $e) {
            throw new NotFoundHttpException('Source image could not be found', $e);
        }

        return $binary;
    }

    protected function storeImageInCache($path, $binary, $filter): void
    {
        try {
            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter),
                $path,
                $filter
            );
        } catch (\RuntimeException $e) {
            $msg = 'Unable to create image for path "%s" and filter "%s". '.'Message was "%s"';
            throw new \RuntimeException(sprintf($msg, $path, $filter, $e->getMessage()), 0, $e);
        }
    }

    protected function imgToWebP(MediaInterface $media, string $filter): void
    {
        $path = $this->projectDir.'/public/'.$media->getRelativeDir().'/'.$filter.'/'.$media->getMedia();

        $webPConverter = new WebPConverter(
            $path,
            $this->projectDir.'/public/'.$media->getRelativeDir().'/'.$filter.'/'.$media->getSlug().'.webp',
            $this->webPConverterOptions
        );

        try {
            $webPConverter->doConvert();
        } catch (\Exception $e) {
            $msg = 'Unable to create image for path "%s" and filter "%s". '.'Message was "%s"';
            throw new \RuntimeException(sprintf($msg, $path, $filter, $e->getMessage()), 0, $e);
        }
    }

    protected function createWebP(MediaInterface $media): void
    {
        $destination = $this->projectDir.'/'.$media->getRelativeDir().'/'.$media->getSlug().'.webp';
        $source = $this->projectDir.'/'.$media->getRelativeDir().'/'.$media->getMedia();
        $webPConverter = new WebPConverter($source, $destination, $this->webPConverterOptions);

        try {
            $webPConverter->doConvert();
        } catch (\Exception $e) {
            $msg = 'Unable to create image for path "%s" and filter "%s". '.'Message was "%s"';
            throw new \RuntimeException(sprintf($msg, $source, 'importing from img', $e->getMessage()), 0, $e);
        }

        $this->webPConverterOptions = ['converters' => [$webPConverter->getConverterUsed()]];
    }
}
