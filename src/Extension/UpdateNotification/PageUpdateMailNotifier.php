<?php

namespace PiedWeb\CMSBundle\Extension\UpdateNotification;

use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Service\App;
use PiedWeb\CMSBundle\Utils\LastTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageUpdateMailNotifier
{
    private $mailer;
    private $emailTo;
    private $emailFrom;
    private $appName;
    private $rootDir;
    private $interval;
    private $em;
    private $translator;
    private $pageClass;
    private $app;
    /** @var App */
    private $apps;

    public function __construct(
        string $pageClass,
        MailerInterface $mailer,
        App $apps,
        string $rootDir,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->mailer = $mailer;
        $this->apps = $apps;
        $this->rootDir = $rootDir;
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->pageClass = $pageClass;
    }

    protected function getPageUpdatedSince($datetime)
    {
        $query = $this->em->createQuery(
            'SELECT p FROM '.$this->pageClass.' p WHERE p.createdAt > :lastTime OR p.updatedAt > :lastTime'
        )->setParameter('lastTime', $datetime);

        return $query->getResult();
    }

    public function postUpdate($page)
    {
        $this->instantiateApp($page);
        $this->send();
    }

    public function postPersist($page)
    {
        $this->instantiateApp($page);
        $this->send();
    }

    protected function instantiateApp($page)
    {
        $this->app = $this->apps->switchCurrentApp($page->getHost())->get();
        $this->emailFrom = $this->app->get('notifier_email');
        $this->emailTo = $this->app->get('page_update_notification_mail');
        $this->interval = $this->app->get('page_update_notification_interval');
        $this->appName = $this->app->get('name');
    }

    public function send()
    {
        if (! $this->emailTo) {
            return;
        }

        $lastTime = new LastTime($this->rootDir.'/../var/lastPageUpdateNotification'.md5($this->app->getHost()));
        if (false === $lastTime->wasRunSince(new DateInterval($this->interval))) {
            return;
        }

        $pages = $this->getPageUpdatedSince($lastTime->get('15 minutes ago'));
        if (empty($pages)) {
            return;
        }

        $message = (new TemplatedEmail())
            ->subject(
                $this->translator->trans('admin.page.update_notification.title', ['%appName%' => $this->appName])
            )
            ->from($this->emailFrom)
            ->to($this->emailTo)
            ->htmlTemplate('@pwcUpdateNotification/pageUpdateMailNotification.html.twig')
            ->context([
                'appName' => $this->appName,
                'pages' => $pages,
            ]);

        $lastTime->set();
        $this->mailer->send($message);
    }
}
