<?php

namespace PiedWeb\CMSBundle\EventListener;

use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PiedWeb\CMSBundle\Entity\MediaInterface;
use PiedWeb\CMSBundle\Service\WebPConverter;
use Liip\ImagineBundle\Async\Commands;
use Liip\ImagineBundle\Async\ResolveCache;
//use WebPConvert\Convert\Converters\Stack as WebPConverter;
use Spatie\Async\Pool;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Process\Process;

use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;

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
        $filters = array_keys($this->filterManager->getFilterConfiguration()->all());

        $path = $media->getPath();
        //$binary = $this->getBinary($path);
        //$pathWebP = '/'.$media->getRelativeDir().'/'.$media->getSlug().'.webp';

        $this->stopWatch = new Stopwatch();
        $this->stopWatch->start('fdb');
        $period = 0;
        echo PHP_EOL;


        $this->createWebP($media);

        $event = $this->stopWatch->lap('fdb');
        echo 'duplicate default media in WebP :'.PHP_EOL;
        echo $event->getPeriods()[$period++]->getDuration().PHP_EOL;


        /**/
        $i=0;
        foreach ($filters as $filter) {
            ++$i;
            //$this->storeImageInCache($path, $binary, $filter);
            /**/
            // this is a bit quicker but less optimized (more CPU usage and more Threads) =>
            ${'process' . $i} = new Process([$this->projectDir.'/bin/console', 'liip:imagine:cache:resolve "'.$path.'" --force --filter='.$filter]);
            //.' >/dev/null 2>&1 &');
            ${'process' . $i}->disableOutput();
            ${'process' . $i}->start();
            /**/
        }

        $event = $this->stopWatch->lap('fdb');
        echo 'init process for jpg filters :'.PHP_EOL;
        echo $event->getPeriods()[$period++]->getDuration().PHP_EOL;

        for ($j = 1; $j < $i; $j++) { ${'process' . $j}->wait(); } /**/


        $event = $this->stopWatch->lap('fdb');
        echo 'wait for jpg :'.PHP_EOL;
        echo $event->getPeriods()[$period++]->getDuration().PHP_EOL;

         foreach ($filters as $filter) {
            $this->imgToWebP($media, $filter);
            //$this->storeImageInCache($pathWebP, $binary, $filter); liip not optimized...
        }


        $event = $this->stopWatch->lap('fdb');
        echo 'init process for webp filters :'.PHP_EOL;
        echo $event->getPeriods()[$period++]->getDuration().PHP_EOL;

        $this->pool->wait();


        $event = $this->stopWatch->lap('fdb');
        echo 'wait for webp :'.PHP_EOL;
        echo $event->getPeriods()[$period++]->getDuration().PHP_EOL;


        $event = $this->stopWatch->stop('fdb');
        echo 'total :'.PHP_EOL;
        echo $event->getDuration().PHP_EOL;
        exit;
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

    protected function imgToWebP(MediaInterface $media, string $filter): void
    {
        $path = $media->getPath();
        $pathJpg = $this->projectDir.'/public/'.$media->getRelativeDir().'/'.$filter.'/'.$media->getMedia();
        $pathWebP = $this->projectDir.'/public/'.$media->getRelativeDir().'/'.$filter.'/'.$media->getSlug().'.webp';
        $webPConverterOptions = self::$webPConverterOptions;
        $projectDir = $this->projectDir;
        //var_dump($path); exit;
        $this->pool->add(function () use ($projectDir, $path, $pathJpg, $pathWebP, $webPConverterOptions, $filter) {
            // took 46s (vs 43s) to add liip generation in async
            //exec($projectDir.'/bin/console liip:imagine:cache:resolve "'.$path.'" --force --filter='.$filter.' >/dev/null 2>&1 &');
            self::imgToWebPStatic($pathJpg, $pathWebP, $webPConverterOptions, $filter);
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

    protected function createWebP(MediaInterface $media): void
    {
        $destination = $this->projectDir.'/'.$media->getRelativeDir().'/'.$media->getSlug().'.webp';
        $source = $this->projectDir.$media->getPath();
        self::createWebPStatic($destination, $source);
        /**
        $this->pool->add(function () use ($destination, $source) {
            self::createWebPStatic($destination, $source);
        });/**/
    }
}
