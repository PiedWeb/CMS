<?php

namespace PiedWeb\CMSBundle\Service;

use Exception;
use PiedWeb\CMSBundle\Entity\PageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment as Twig;

class App
{
    protected $host;
    protected $apps;
    protected $app;

    /** @var PageInterface */
    protected $currentPage;

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
            if ($host instanceof PageInterface) {
                $this->currentPage = $host;
                $host = $host->getHost();
            }
            $this->host = $host;
        }

        foreach ($this->apps as $app) {
            if (\in_array($this->host, $app['hosts']) || null === $this->host) {
                $this->app = $app;

                return $this;
            }
        }

        throw new Exception('Unconfigured host `'.$this->host.'`. See config/packages/piedweb_cms.yaml');
    }

    public function isFirstApp(): bool
    {
        return $this->getFirstHost() === $this->getMainHost();
    }

    public function getFirstHost()
    {
        return $this->apps[array_key_first($this->apps)]['hosts'][0];
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getMainHost(): string
    {
        return $this->app['hosts'][0];
    }

    /**
     * Used in Router Extension.
     *
     * @return bool
     */
    public function isMainHost($host)
    {
        return $this->getMainHost() === $host;
        //|| ($this->isFirstApp() && $this->getHost() === null);
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

    /**
     * @psalm-suppress InternalMethod
     */
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
            $twig->loadTemplate($name);

            return $name;
        } finally {
            return '@PiedWebCMS'.$path;
        }
    }

    public function isFullPath($path)
    {
        return 0 === strpos($path, '@') && false !== strpos($path, '/');
    }

    /**
     * Get the value of currentPage.
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the value of apps.
     */
    public function getApps()
    {
        return $this->apps;
    }
}
