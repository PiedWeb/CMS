<?php

namespace PiedWeb\CMSBundle\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigHelper
{
    protected $request;
    protected $params;
    protected $appConfig;

    public static function get(Request $request, ParameterBagInterface $params): self
    {
        return new self($request, $params);
    }

    public function __construct(Request $request, ParameterBagInterface $params)
    {
        $this->request = $request;
        $this->params = $params;

        foreach ($params->get('pwc.apps') as $app) {
            if (in_array($request->getHost(), $app['hosts'])) {
                $this->appConfig = $app;
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
        return $this->params->get('pwc.apps')[array_key_first($this->params->get('pwc.apps'))]['hosts'][0];
    }

    public function getHost(): string
    {
        foreach ($this->params->get('pwc.apps') as $app) {
            if (in_array($this->request->getHost(), $app['hosts'])) {
                return $app['hosts'][0];
            }
        }

        throw new Exception('Unconfigured host `'.$this->request->getHost().'`. See piedweb_cms.yaml');
    }

    public function getBaseUrl(): string
    {
        return $this->appConfig['base_url'];
    }

    public function getApp($key)
    {
        return $this->appConfig[$key];
    }

    public function getDefaultTemplate()
    {
        return  $this->appConfig['default_page_template'] ?? $this->params->get('pwc.default_page_template');
    }
}
