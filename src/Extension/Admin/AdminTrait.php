<?php

namespace PiedWeb\CMSBundle\Extension\Admin;

trait AdminTrait
{
    protected $apps;
    protected $pageCanonicalService;
    protected $pageClass;
    protected $defaultLocale;
    protected $mediaClass;
    protected $userClass;
    protected $twig;
    protected $em;
    protected $securityTokenStorage;
    protected $defaultMainContentType;

    protected static $thumb = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaGVpZ2h0PSIzMnB4IiB2ZXJzaW
                9uPSIxLjEiIHZpZXdCb3g9IjAgMCAzMiAzMiIgd2lkdGg9IjMycHgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIg
                eG1sbnM6c2tldGNoPSJodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2gvbnMiIHhtbG5zOnhsaW5rPSJodHRwOi8
                vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48dGl0bGUvPjxkZXNjLz48ZGVmcy8+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub
                2RkIiBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSI+PGcgZmlsbD0iIzkyOTI5MiIgaWQ9Imljb24
                tMjEtZXllLWhpZGRlbiI+PHBhdGggZD0iTTguMTA4Njk4OTEsMjAuODkxMzAxMSBDNC42MTcyMDgxNiwxOC44MzAxMTQ3IDMsMT
                YgMywxNiBDMywxNiA3LDkgMTYsOSBDMTcuMzA0NTEwNyw5IDE4LjUwMzk3NTIsOS4xNDcwNjQ2NiAxOS42MDE0Mzg4LDkuMzk4
                NTYxMjIgTDE4Ljc1MTkwMTcsMTAuMjQ4MDk4MyBDMTcuODk3MTQ4NCwxMC4wOTAwNTQ2IDE2Ljk4MDA5MjksMTAgMTYsMTAgQzg
                sMTAgNC4xOTk5NTExNywxNiA0LjE5OTk1MTE3LDE2IEM0LjE5OTk1MTE3LDE2IDUuNzE0NzI4MDgsMTguMzkxNzIyNSA4Ljg0ND
                kyNzEzLDIwLjE1NTA3MjkgTDguMTA4Njk4OTEsMjAuODkxMzAxMSBMOC4xMDg2OTg5MSwyMC44OTEzMDExIEw4LjEwODY5ODkxLD
                IwLjg5MTMwMTEgWiBNMTIuMzk4NTYxLDIyLjYwMTQzOSBDMTMuNDk2MDI0NiwyMi44NTI5MzU2IDE0LjY5NTQ4OTIsMjMuMDAwMD
                AwMSAxNiwyMyBDMjUsMjIuOTk5OTk5IDI5LDE2IDI5LDE2IEMyOSwxNiAyNy4zODI3OTE4LDEzLjE2OTg4NTYgMjMuODkxMzAwOC
                wxMS4xMDg2OTkyIEwyMy4xNTUwNzI3LDExLjg0NDkyNzMgQzI2LjI4NTI3MTksMTMuNjA4Mjc3NiAyNy44MDAwNDg4LDE2IDI3Lj
                gwMDA0ODgsMTYgQzI3LjgwMDA0ODgsMTYgMjQsMjEuOTk5OTk5IDE2LDIyIEMxNS4wMTk5MDcsMjIuMDAwMDAwMSAxNC4xMDI4N
                TE1LDIxLjkwOTk0NTUgMTMuMjQ4MDk4MSwyMS43NTE5MDE5IEwxMi4zOTg1NjEsMjIuNjAxNDM5IEwxMi4zOTg1NjEsMjIuNjAxN
                DM5IEwxMi4zOTg1NjEsMjIuNjAxNDM5IFogTTE5Ljg5ODY1MzEsMTUuMTAxMzQ2OSBDMTkuOTY0OTY1OCwxNS4zOTAyMTE1IDIwL
                DE1LjY5MTAxNDQgMjAsMTYgQzIwLDE4LjIwOTEzOTEgMTguMjA5MTM5MSwyMCAxNiwyMCBDMTUuNjkxMDE0NCwyMCAxNS4zOTAyM
                TE1LDE5Ljk2NDk2NTggMTUuMTAxMzQ2OSwxOS44OTg2NTMxIEwxNiwxOSBDMTYuNzY3NzY2OSwxOS4wMDAwMDAxIDE3LjUzNTUzM
                zksMTguNzA3MTA2OCAxOC4xMjEzMjAzLDE4LjEyMTMyMDMgQzE4LjcwNzEwNjgsMTcuNTM1NTMzOSAxOS4wMDAwMDAxLDE2Ljc2N
                zc2NjkgMTksMTYgTDE5Ljg5ODY1MzEsMTUuMTAxMzQ2OSBMMTkuODk4NjUzMSwxNS4xMDEzNDY5IEwxOS44OTg2NTMxLDE1LjEwM
                TM0NjkgWiBNMTYuODk4NjUzMSwxMi4xMDEzNDY5IEMxNi42MDk3ODg1LDEyLjAzNTAzNDIgMTYuMzA4OTg1NiwxMiAxNiwxMiBDM
                TMuNzkwODYwOSwxMiAxMiwxMy43OTA4NjA5IDEyLDE2IEMxMiwxNi4zMDg5ODU2IDEyLjAzNTAzNDIsMTYuNjA5Nzg4NSAxMi4xM
                DEzNDY5LDE2Ljg5ODY1MzEgTDEzLDE2IEMxMi45OTk5OTk5LDE1LjIzMjIzMzEgMTMuMjkyODkzMiwxNC40NjQ0NjYxIDEzLjg3O
                DY3OTcsMTMuODc4Njc5NyBDMTQuNDY0NDY2MSwxMy4yOTI4OTMyIDE1LjIzMjIzMzEsMTIuOTk5OTk5OSAxNiwxMyBMMTYuODk4N
                jUzMSwxMi4xMDEzNDY5IEwxNi44OTg2NTMxLDEyLjEwMTM0NjkgTDE2Ljg5ODY1MzEsMTIuMTAxMzQ2OSBaIE0yNCw3IEw3LDI0I
                Ew4LDI1IEwyNSw4IEwyNCw3IEwyNCw3IFoiIGlkPSJleWUtaGlkZGVuIi8+PC9nPjwvZz48L3N2Zz4=';

    abstract public function setListMode($mode);

    /**
     * Must be a cookie to check before to do that
     * If you click one time to list, stay in liste mode.
     * Yes it's in the session
     * TODO.
     * */
    protected function setMosaicDefaultListMode(): self
    {
        if (null !== $this->request) {
            if ($mode = $this->request->query->get('_list_mode')) {
                $this->setListMode($mode);
            } else {
                $this->setListMode('mosaic');
            }
        }

        return $this;
    }

    protected function getUser()
    {
        return $this->securityTokenStorage->getToken()->getUser();
    }

    public function setSecurityTokenStorage($securityTokenStorage)
    {
        $this->securityTokenStorage = $securityTokenStorage;
    }

    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
    }

    public function setTwig($twig)
    {
        $this->twig = $twig;
    }

    public function setPageClass($pageClass)
    {
        $this->pageClass = $pageClass;
    }

    public function setApps($apps)
    {
        $this->apps = $apps;
    }

    public function setMediaClass($mediaClass)
    {
        $this->mediaClass = $mediaClass;
    }

    public function setUserClass($userClass)
    {
        $this->userClass = $userClass;
    }

    public function setPageCanonicalService($pageCanonicalService)
    {
        $this->pageCanonicalService = $pageCanonicalService;
    }

    public function setDefaultMainContentType($defaultMainContentType)
    {
        $this->defaultMainContentType = $defaultMainContentType;
    }
}
