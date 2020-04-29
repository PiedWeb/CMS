<?php

namespace PiedWeb\CMSBundle\EventListener;

use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PiedWeb\CMSBundle\Entity\MediaInterface;
use PiedWeb\CMSBundle\Service\WebPConverter;
//use WebPConvert\Convert\Converters\Stack as WebPConverter;
use Spatie\Async\Pool;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @require dataManager
 * cacheManager
 * filterManager
 */
trait MediaCacheGeneratorTrait
{
    protected $projectDir;

    protected static $webPConverterOptions = [
        'converters' => ['cwebp'],
        //'try-cwebp'  => false,
        //'converters' => ['cwebp', 'gd', 'vips', 'imagick', 'gmagick', 'imagemagick', 'graphicsmagick', 'wpc', 'ewww'],
    ];

    protected function generateCache(MediaInterface $media)
    {
        $this->pool = Pool::create();

        $this->createWebP($media); // do i need it ?! Yes, if the generation failed, liip will use this one

        $path = '/'.$media->getRelativeDir().'/'.$media->getMedia();
        $binary = $this->getBinary($path);
        //$pathWebP = '/'.$media->getRelativeDir().'/'.$media->getSlug().'.webp';

        $filters = array_keys($this->filterManager->getFilterConfiguration()->all());

        foreach ($filters as $filter) {
            $this->storeImageInCache($path, $binary, $filter);
            $this->imgToWebP($media, $filter);
            //$this->storeImageInCache($pathWebP, $binary, $filter); liip not optimized...
        }
        $this->pool->wait();
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

    protected static function storeImageInCacheStatic($path, $binary, $filter, $cacheManagerManager): void
    {
        try {
            $cacheManager->store(
                $filterManager->applyFilter($binary, $filter),
                $path,
                $filter
            );
        } catch (\RuntimeException $e) {
            $msg = 'Unable to create image for path "%s" and filter "%s". '.'Message was "%s"';
            throw new \RuntimeException(sprintf($msg, $path, $filter, $e->getMessage()), 0, $e);
        }
    }

    protected static function imgToWebPStatic($path, $webPPath, $webPConverterOptions, string $filter): void
    {
        $webPConverter = new WebPConverter(
            $path,
            $webPPath,
            $webPConverterOptions
        );

        try {
            $webPConverter->doConvert();
        } catch (\Exception $e) {
            $msg = 'Unable to create image for path "%s" and filter "%s". '.'Message was "%s"';
            throw new \RuntimeException(sprintf($msg, $path, $filter, $e->getMessage()), 0, $e);
        }
    }

    /**
     * Use the liip generated filter to generate the webp equivalent.
     */
    protected function imgToWebP(MediaInterface $media, string $filter): void
    {
        $path = $media->getPath();
        $pathJpg = $this->projectDir.'/public/'.$media->getRelativeDir().'/'.$filter.'/'.$media->getMedia();
        $pathWebP = $this->projectDir.'/public/'.$media->getRelativeDir().'/'.$filter.'/'.$media->getSlug().'.webp';
        $webPConverterOptions = self::$webPConverterOptions;
        $projectDir = $this->projectDir;

        $this->pool->add(function () use ($projectDir, $path, $pathJpg, $pathWebP, $webPConverterOptions, $filter) {
            // took 46s (vs 43s) to add liip generation in async
            //exec($projectDir.'/bin/console liip:imagine:cache:resolve "'.$path.'" --force --filter='.$filter
            //.' >/dev/null 2>&1 &');
            self::imgToWebPStatic($pathJpg, $pathWebP, $webPConverterOptions, $filter);
        });
    }

    protected function createWebP(MediaInterface $media): void
    {
        $destination = $this->projectDir.'/'.$media->getRelativeDir().'/'.$media->getSlug().'.webp';
        $source = $this->projectDir.$media->getPath();
        //self::createWebPStatic($destination, $source);

        $this->pool->add(function () use ($destination, $source) {
            self::createWebPStatic($destination, $source);
        });
    }

    protected function createWebPStatic($destination, $source): void
    {
        $webPConverter = new WebPConverter($source, $destination, self::$webPConverterOptions);

        try {
            $webPConverter->doConvert();
        } catch (\Exception $e) {
            $msg = 'Unable to create image for path "%s" and filter "%s". '.'Message was "%s"';
            throw new \RuntimeException(sprintf($msg, $source, 'importing from img', $e->getMessage()), 0, $e);
        }
    }
}
