<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Media;
use App\Entity\User;
use App\Entity\Page;
use DateTime;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = (new User())
            ->setEmail('contact@piedweb.com')
            ->setRoles([User::ROLE_DEFAULT]);

        $manager->persist($user);


        $media = (new Media())
            ->setRelativeDir('/media')
            ->setMimeType('image/png')
            ->setSize(2)
            ->setSlug('piedweb-logo.png')
            ->setMedia('piedweb-logo.png')
            ->setName('Pied Web Logo');

        $manager->persist($media);

        $page = (new Page())
            ->setH1('Welcome : this is your first page')
            ->setSlug('homepage')
            ->setLocale('en')
            ->setCreatedAt(new DateTime('2 days ago'))
            ->setMainContent(file_get_contents(__DIR__.'/WelcomePageMainContent.md'))
            ->setMainContentIsMarkdown(true);

        $manager->persist($page);

        $manager->flush();
    }
}