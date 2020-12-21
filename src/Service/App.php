<?php

namespace PiedWeb\CMSBundle\Service;

use Exception;
use PiedWeb\CMSBundle\Entity\PageInterface;

class App
{
    protected $apps = [];
    protected $currentApp;
    protected $host; // often same as currentApp

    /**
     * Why there ? Because often, need to check current page don't override App Config.
     *
     *  @var PageInterface */
    protected $currentPage;

    public function __construct(?string $host, array $apps)
    {
        $this->host = $host;

        foreach ($apps as $mainHost => $app) {
            $this->apps[$mainHost] = new AppConfig($app, array_key_first($apps) == $mainHost ? true : false);
        }

        $this->switchCurrentApp();
    }

    public function switchCurrentApp($host = null): self
    {
        if ($host instanceof PageInterface) {
            $this->currentPage = $host;
            $host = $host->getHost();
        }
        $this->host = $host;

        $app = $this->get($this->host, false);

        if (! $app) {
            throw new Exception('Unconfigured host `'.$this->host.'`. See config/packages/piedweb_cms.yaml');
        }
        $this->currentApp = $app->getMainHost();

        return $this;
    }

    public function get($host = null, $isMainHost = true)
    {
        if ($isMainHost) {
            return $this->apps[null === $host ? $this->currentApp : $host];
        }

        foreach ($this->apps as $app) {
            if (\in_array($host, $app->getHosts()) || null === $host) {
                return $app;
            }
        }
    }

    public function getHosts(): array
    {
        return array_keys($this->apps);
    }

    /**
     * Get the value of apps.
     */
    public function getApps(): array
    {
        return $this->apps;
    }

    /**
     * Get the value of currentPage.
     */
    public function getCurrentPage(): ?PageInterface
    {
        return $this->currentPage;
    }

    public function isFirstApp($host = null): bool
    {
        $firstApp = array_key_first($this->apps);

        if (null === $host) {
            return $firstApp === $this->get()->getMainHost();
        }

        if (\is_string($host)) {
            return $firstApp === $host;
        }

        foreach ($host as $h) {
            if ($firstApp === $h) {
                return true;
            }
        }

        return false;
    }

    /**
     * Alias for ->get()->getMainHost().
     */
    public function getMainHost(): ?string
    {
        return $this->currentApp;
    }

    /**
     * Alias for self::get()->get($key).
     */
    public function getApp(string $key)
    {
        return $this->apps[$key]->get($key);
    }
}
