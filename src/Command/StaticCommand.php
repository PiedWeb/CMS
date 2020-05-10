<?php

namespace PiedWeb\CMSBundle\Command;

use PiedWeb\CMSBundle\Service\StaticService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StaticCommand extends Command
{
    private $static;

    public function __construct(StaticService $static)
    {
        parent::__construct();
        $this->static = $static;
    }

    protected function configure()
    {
        $this
            ->setName('static:generate')
            ->setDescription('Generate static version  for your website')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->static->dump();
        $output->writeln('Static version generation succeeded.');
    }
}
