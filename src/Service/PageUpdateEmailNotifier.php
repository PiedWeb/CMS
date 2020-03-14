<?php

namespace PiedWeb\CMSBundle\Service;

use DateTime;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Move it to a plugin (todo).
 */
class PageUpdateEmailNotifier
{
    private $mailer;
    private $emailSender;
    private $emailToNotify;
    private $router;
    private $appName;
    private $rootDir;
    private $filesystem;

    public function __construct(
        string $appName,
        string $emailSender,
        string $emailToNotify,
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        string $rootDir
    ) {
        $this->mailer = $mailer;
        $this->emailSender = $emailSender;
        $this->emailToNotify = $emailToNotify;
        $this->appName = $appName;
        $this->router = $router;
        $this->rootDir = $rootDir;
        $this->filesystem = new Filesystem();
    }

    /**
     * Send max 1 notification per hour.
     */
    protected function isItTimeTonotify(): bool
    {
        $lastNotificationFilepath = $this->rootDir.'/../var/lastPageUpdateNotification';

        if (file_exists($lastNotificationFilepath)) {
            $lastNotificationSendAt = new DateTime(date('Y-m-d H:i:s.', filemtime($lastNotificationFilepath)));
            $now = new DateTime('now');
            if ($lastNotificationSendAt->diff($now)->format('%h') < 1) {
                return false;
            }
        } else {
            file_put_contents($lastNotificationFilepath, '');
        }

        $this->filesystem->touch($lastNotificationFilepath);

        return true;
    }

    public function postUpdate($page)
    {
        if (!$this->emailToNotify || false === $this->isItTimeTonotify()) {
            return;
        }

        $adminUrl = $this->router->generate(
            'admin_app_page_edit',
            ['id' => $page->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = (new \Swift_Message('Update on '.$this->appName))
        ->setFrom($this->emailSender)
        ->setTo($this->emailToNotify)
        ->setBody(
            'La page `<a href="'.$adminUrl.'">'.$page->getSlug().'</a>` a été modifiée.',
            'text/html'
        );

        $this->mailer->send($message);
    }
}
