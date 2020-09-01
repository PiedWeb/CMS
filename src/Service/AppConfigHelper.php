<?php

namespace PiedWeb\CMSBundle\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class AppConfigHelper
{
    protected $host;
    protected $apps;
    protected $app;

    /**
     * static loader.
     *
     * @param mixed $request could be Reqest or a string with the current host
     * @param mixed
     */
    public static function get($host, $apps): self
    {
        return new self(
            $host instanceof Request ? $host->getHost() : $host,
            $apps instanceof ParameterBagInterface ? $apps->get('pwc.apps') : $apps
        );
    }

    public function __construct(?string $host, array $apps)
    {
        $this->host = $host;
        $this->apps = $apps;

        $this->loadCurrentApp();
    }

    protected function loadCurrentApp()
    {
        foreach ($this->apps as $app) {
            if (in_array($this->host, $app['hosts']) || null === $this->host) {
                $this->app = $app;
                break;
            }
        }
    }

    public function isFirstApp(): bool
    {
        return $this->getFirstHost() === $this->getHost();
    }

    public function getFirstHost()
    {
        return $this->apps[array_key_first($this->apps)]['hosts'][0];
    }

    public function getHost(): string
    {
        foreach ($this->apps as $app) {
            if (in_array($this->host, $app['hosts']) || null === $this->host) {
                return $app['hosts'][0];
            }
        }

        throw new Exception('Unconfigured host `'.$this->host.'`. See piedweb_cms.yaml');
    }

    public function getBaseUrl(): string
    {
        return $this->app['base_url'];
    }

    public function getApp($key)
    {
        return $this->app[$key];
    }

    public function getDefaultTemplate()
    {
        return  $this->app['default_page_template'];
    }
}
