<?php

namespace PiedWeb\CMSBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

class ImagineWebCacheResolver extends \Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver //implements ResolverInterface
{
    public function __construct(
        Filesystem $filesystem,
        RequestContext $requestContext,
        $webRootDir,
        $cachePrefix = 'media'
    ) {
        $this->filesystem = $filesystem;
        $this->requestContext = $requestContext;
        $this->webRoot = rtrim(str_replace('//', '/', $webRootDir), '/');
        $this->cachePrefix = ltrim(str_replace('//', '/', $cachePrefix), '/');
        $this->cacheRoot = $this->webRoot.'/'.$this->cachePrefix;
    }

    public function resolve($path, $filter)
    {
        return '/'.$this->getFileUrl($path, $filter);
    }
}
