<?php

namespace PiedWeb\CMSBundle\Extension\PageScanner;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class PageScannerCommand extends Command
{
    private $filesystem;
    private $scanner;
    private $pageClass;
    private $em;

    public function __construct(
        PageScannerService $scanner,
        Filesystem $filesystem,
        EntityManagerInterface $em,
        string $pageClass,
        string $varDir
    ) {
        parent::__construct();
        $this->scanner = $scanner;
        $this->pageClass = $pageClass;
        $this->em = $em;
        $this->filesystem = $filesystem;
        PageScannerController::$fileCache = $varDir.PageScannerController::$fileCache;
    }

    protected function configure()
    {
        $this
            ->setName('page:scan')
            ->addArgument('host', InputArgument::OPTIONAL);
    }

    protected function scanAllWithLock()
    {
        $lock = (new LockFactory(new FlockStore()))->createLock('page-scan');
        if ($lock->acquire()) {
            sleep(30);
            $errors = $this->scanAll();
            $this->filesystem->dumpFile(PageScannerController::$fileCache, serialize($errors));
            $lock->release();

            return true;
        }

        return false;
    }

    protected function scanAll()
    {
        $pages = $this->em->getRepository($this->pageClass)->findAll();

        $errors = [];
        $errorNbr = 0;

        foreach ($pages as $page) {
            // todo import scanner via setScanner + services.yaml
            $scan = $this->scanner->scan($page);
            if (true !== $scan) {
                $errors[$page->getId()] = $scan;
                $errorNbr = $errorNbr + \count($errors[$page->getId()]);
            }

            if ($errorNbr > 500) {
                break;
            }
        }

        return $errors;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //if ($input->getArgument('host'))

        if ($this->scanAllWithLock()) {
            $output->writeln('done...');
        } else {
            $output->writeln('cannot acquire the lock...');
        }

        return 0;
    }
}
