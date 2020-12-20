<?php

namespace PiedWeb\CMSBundle\Service;

use Twig\Environment as Twig;

class AppConfig
{
    protected $isFirstApp = false;
    protected $hosts;
    protected $customProperties;
    protected $locale;
    protected $locales;
    protected $baseUrl;
    protected $name;
    protected $template;
    // This app conf are more customProperties... todo ?!
    protected $canUseTwigShortcode;
    protected $mainContentType;

    protected static function normalizePropertyName(string $string): string
    {
        $string = str_replace('_', '', ucwords(strtolower($string), '_'));
        $string = lcfirst($string);

        return $string;
    }

    public function __construct($properties, $isFirstApp = false)
    {
        foreach ($properties as $prop => $value) {
            $prop = static::normalizePropertyName($prop);
            $this->$prop = $value;
        }

        $this->isFirstApp = $isFirstApp;
    }

    public function getParamsForRendering(): array
    {
        return [
            'app_base_url' => $this->getBaseUrl(),
            'app_name' => $this->name,
            'app_color' => $this->getCustomProperty('color'),
        ];
    }

    /**
     * Todo : change for getHost ?!
     */
    public function getMainHost(): string
    {
        return $this->hosts[0];
    }

    /**
     * Used in Router Extension.
     *
     * @return bool
     */
    public function isMainHost($host)
    {
        return $this->getMainHost() === $host;
    }

    public function getHosts()
    {
        return $this->hosts;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function get(string $key)
    {
        $method = 'get'.ucfirst(static::normalizePropertyName($key));

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return $this->getCustomProperty($key);
    }

    public function getCustomProperty(string $key)
    {
        return isset($this->customProperties[$key]) ? $this->customProperties[$key] : null;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @psalm-suppress InternalMethod
     */
    public function getView(?string $path = null, ?Twig $twig = null) // todo : make twig global
    {
        if (null === $path) {
            return $this->template.'/page/page.html.twig';
        }

        if ($this->isFullPath($path)) { // permits to get a component from a dedicated extension eg @pwcEgTheme/page...
            return $path;
        }

        if ($overrided = null !== $this->getOverridedView($path)) {
            return $overrided;
        }

        $name = $this->template.$path;

        if (null === $twig || '@PiedWebCMS' == $this->template) {
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

    protected function getOverridedView(string $name)
    {
        $templateDir = './templates'; // TODO load it from conf or maybe it's in twig ?!

        $templateOverrided = $templateDir.'/'.ltrim($this->getTemplate(), '@').$name;
        if (file_exists($templateOverrided)) {
            return $templateOverrided;
        }

        $globalOverride = $templateDir.$name;
        if (file_exists($globalOverride)) {
            return $globalOverride;
        }
    }

    protected function isFullPath($path)
    {
        return 0 === strpos($path, '@') && false !== strpos($path, '/');
    }

    public function isFirstApp(): bool
    {
        return $this->isFirstApp;
    }

    /**
     * Get the value of locale.
     */
    public function getLocale()
    {
        return $this->locale;
    }

    public function getDefaultLocale()
    {
        return $this->locale;
    }

    /**
     * Get the value of locales.
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Get the value of name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of mainContentType.
     */
    public function getMainContentType()
    {
        return $this->getCustomProperty('main_content_type');
    }

    public function canUseTwigShortcode(): bool
    {
        return $this->getCustomProperty('can_use_twig_shortcode');
    }
}
