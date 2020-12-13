<?php

namespace PiedWeb\CMSBundle\Utils;

use DateInterval;
use DateTime;

/**
 * Usage
 * (new LastTime($rootDir.'/../var/lastNoficationUpdatePageSendAt'))->wasRunSince(new DateInterval('P2H')).
 */
class LastTime
{
    protected $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath; //$rootDir.'/../var/last/'.$actionName;
    }

    public function wasRunSince(DateInterval $interval): bool
    {
        $previous = $this->get();

        if (false !== $previous
            && $previous->add($interval) > new DateTime('now')) {
            return false;
        }

        return true;
    }

    /**
     * Return false if never runned else last datetime it was runned.
     */
    public function get($default = false)
    {
        if (!file_exists($this->filePath)) {
            return false === $default ? false : new DateTime($default);
        }

        return new DateTime(file_get_contents($this->filePath));
    }

    public function set($datetime = 'now')
    {
        file_put_contents($this->filePath, (new DateTime($datetime))->format('Y-m-d H:i:s'));
    }
}
