<?php

namespace PiedWeb\CMSBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use Twig_Environment;

/**
 * Permit to find error in image or link.
 */
class PageScannerService
{
    protected $em;
    protected $pageHtml;
    protected $twig;
    protected $defaultTemplate;
    protected $currentPage;
    protected $webDir;
    protected $errors = [];
    protected $everChecked = [];

    public function __construct(
        Twig_Environment $twig,
        EntityManagerInterface $em,
        string $defaultTemplate,
        string $webDir
    ) {
        $this->twig = $twig;
        $this->em = $em;
        $this->defaultTemplate = $defaultTemplate;
        $this->webDir = $webDir;
    }

    public function scan(Page $page)
    {
        $this->currentPage = $page;
        $this->errors = [];
        $this->pageHtml = '';

        if (false !== $page->getRedirection()) {
            // check $page->getRedirection() return 20X

            return true; // or status code
        }

        $template = null !== $page->getTemplate() ? $page->getTemplate() : $this->defaultTemplate;
        $this->pageHtml = $this->twig->render($template, ['page' => $page]);

        // 2. Je récupère tout les liens et je les check
        // href="", data-rot="" data-img="", src="", data-bg
        $this->checkLinkedDocs($this->getLinkedDocs());

        return empty($this->errors) ? true : $this->errors;
    }

    protected function addError($message)
    {
        $this->errors[] = [
            'message' => $message,
            'page' => $this->currentPage,
        ];
    }

    protected function getLinkedDocs(): array
    {
        preg_match_all('/(href|data-rot|src|data-img|data-bg)=("\/[^"]*|\/[^\s>]*)/i', $this->pageHtml, $matches);

        $linkedDocs = [];
        foreach ($matches[0] as $k => $match) {
            $uri = ltrim('data-rot' == $matches[1][$k] ? str_rot13($matches[2][$k]) : $matches[2][$k], '"');
            $uri = strtok($uri, '#');
            if ('' !== $uri) {
                $linkedDocs[] = $uri;
            }
        }

        return array_unique($linkedDocs);
    }

    protected function checkLinkedDocs(array $linkedDocs)
    {
        foreach ($linkedDocs as $uri) {
            if (!$this->uriExist($uri)) {
                $this->addError('<code>'.$uri.'</code> introuvable');
            }
        }
    }

    protected function uriExist($uri)
    {
        $slug = ltrim($uri, '/');

        if (isset($this->everChecked[$slug])) {
            return $this->everChecked[$slug];
        }

        $checkDatabase = 0 !== strpos($slug, 'media/'); // we avoid to check in db the media, file exists is enough
        $page = true !== $checkDatabase ? null : $this->em->getRepository(get_class($this->currentPage))
            ->findOneBySlug('' == $slug ? 'homepage' : $slug);

        $this->everChecked[$slug] = (null === $page && !file_exists($this->webDir.'/'.$slug)) ? false : true;

        return $this->everChecked[$slug];
    }
}
