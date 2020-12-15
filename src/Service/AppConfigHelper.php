<?php

namespace PiedWeb\CMSBundle\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment as Twig;

class AppConfigHelper
{
    protected $host;
    protected $apps;
    protected $app;

    /**
     * static loader.
     *
     * @param mixed $host could be Reqest or a string with the current host
     * @param mixed $apps
     */
    public static function load($host, $apps): self
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

        $this->switchCurrentApp();
    }

    public function switchCurrentApp($host = null)
    {
        if (null !== $host) {
            $this->host = $host;
        }

        foreach ($this->apps as $app) {
            if (\in_array($this->host, $app['hosts']) || null === $this->host) {
                $this->app = $app;

                return;
            }
        }

        throw new Exception('Unconfigured host `'.$this->host.'`. See config/packages/piedweb_cms.yaml');
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
            if (\in_array($this->host, $app['hosts']) || null === $this->host) {
                return $app['hosts'][0];
            }
        }

        throw new Exception('Unconfigured host `'.$this->host.'`. See piedweb_cms.yaml');
    }

    public function getMainHost(): string
    {
        return $this->app['hosts'][0];
    }

    public function getHosts()
    {
        return $this->app['hosts'];
    }

    public function getBaseUrl(): string
    {
        return $this->app['base_url'];
    }

    public function getStaticDir(): string
    {
        return $this->app['static_dir'];
    }

    public function get($key)
    {
        return $this->app[$key];
    }

    public function getApp($key)
    {
        return $this->app[$key];
    }

    public function getParamsForRendering()
    {
        return [
            'app_base_url' => $this->getBaseUrl(),
            'app_name' => $this->getApp('name'),
            'app_color' => $this->getApp('color'),
        ];
    }

    public function getTemplate(string $path = '/page/page.html.twig', ?Twig $twig = null)
    {
        if ($this->isFullPath($path)) { // compatibilité avant v1
            return $path;
        }

        $name = $this->app['template'].$path;

        if (null === $twig || '@PiedWebCMS' == $this->app['template']) {
            return $name;
        }

        // check if twig template exist
        try {
            return $twig->loadTemplate($name);
        } finally {
            return '@PiedWebCMS'.$path;
        }

        return $name;
    }

    public function isFullPath($path)
    {
        return 0 === strpos($path, '@') && false !== strpos($path, '/');
    }
}
