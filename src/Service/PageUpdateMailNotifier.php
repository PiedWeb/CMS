<?php

namespace PiedWeb\CMSBundle\Service;

use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Move it to a plugin (todo).
 */
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
    private $page;

    public function __construct(
        string $page,
        MailerInterface $mailer,
        string $emailFrom,
        ?string $emailTo,
        string $appName,
        string $rootDir,
        string $interval, //minIntervalBetweenTwoNotification
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->mailer = $mailer;
        $this->emailTo = $emailTo;
        $this->emailFrom = $emailFrom;
        $this->interval = $interval;
        $this->appName = $appName;
        $this->rootDir = $rootDir;
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->page = $page;
    }

    protected function getPageUpdatedSince($lastTime)
    {
        $query = $this->em->createQuery(
            'SELECT p FROM '.$this->page.' p WHERE p.createdAt > :lastTime OR p.updatedAt > :lastTime'
        )->setParameter('lastTime', $lastTime);

        return $query->getResult();
    }

    public function postUpdate($page)
    {
        $this->send();
    }

    public function postPersist($page)
    {
        $this->send();
    }

    public function send()
    {
        if (!$this->emailTo) {
            return;
        }

        $lastTime = new LastTime($this->rootDir.'/../var/lastPageUpdateNotification');
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
            ->htmlTemplate('@PiedWebCMS/admin/pageUpdateMailNotification.html.twig')
            ->context([
                'appName' => $this->appName,
                'pages' => $pages,
            ]);

        $lastTime->set();
        $this->mailer->send($message);
    }
}
