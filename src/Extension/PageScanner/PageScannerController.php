<?php

namespace PiedWeb\CMSBundle\Extension\PageScanner;

use DateInterval;
use PiedWeb\CMSBundle\Utils\LastTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\Filesystem\Filesystem;

class PageScannerController extends AbstractController
{
    /**
     * @var PageScannerService
     */
    protected $scanner;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ContainerBag
     */
    protected $params;
    protected $filesystem;
    protected $eventDispatcher;

    public static $fileCache = '/page-scan';

    public function __construct(
        Filesystem $filesystem,
        string $varDir
    ) {
        $this->filesystem = $filesystem;
        self::$fileCache = $varDir.self::$fileCache;
    }

    public function scanAction()
    {
        if ($this->filesystem->exists(self::$fileCache)) {
            $errors = unserialize(file_get_contents(self::$fileCache));
            $lastEdit = filemtime(self::$fileCache);
        } else {
            $lastEdit = 0;
            $errors = [];
        }

        $lastTime = new LastTime(self::$fileCache);
        if (false === $lastTime->wasRunSince(new DateInterval('PT5M'))) { // todo config
            exec('cd ../ && php bin/console page:scan > /dev/null 2>/dev/null &');
            $newRunLaunched = true;
            $lastTime->setWasRun('now', false);
        }

        return $this->render('@pwcPageScanner/results.html.twig', [
            'newRun' => $newRunLaunched ?? false,
            'lastEdit' => $lastEdit,
            'errorsByPages' => $errors,
        ]);
    }
}
