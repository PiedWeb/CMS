<?php

namespace PiedWeb\CMSBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use PiedWeb\CMSBundle\Command\UserCreateCommand;
use PiedWeb\CMSBundle\Entity\User;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UserCommandTest extends TestCase
{
    public function testExecute(): void
    {
        return; // todo
        $application = new Application();

        $command = new UserCreateCommand([
            // entity Manager
            // password encoder
            // string user class
        ]);

        $application->add($command);

        $command = $application->find('user:create');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(['command' => $command->getName()]);

        $this->assertSame(0, $exitCode, 'Returns 0 in case of success');
    }
}
