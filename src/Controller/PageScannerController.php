<?php

namespace PiedWeb\CMSBundle\Controller;

use PiedWeb\CMSBundle\Service\PageScannerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PageScannerController extends AbstractController
{
    /**
     * @var PageScannerService
     */
    protected $scanner;

    public function __construct(PageScannerService $scanner)
    {
        $this->scanner = $scanner;
    }

    public function scanAction()
    {
        $pages = $this->getDoctrine()
            ->getRepository($this->container->getParameter('pwc.entity_page'))
            ->findAll();

        $errors = [];
        $errorNbr = 0;

        foreach ($pages as $page) {
            // todo import scanner via setScanner + services.yaml
            $scan = $this->scanner->scan($page);
            if (true !== $scan) {
                $errors[$page->getId()] = $scan;
                $errorNbr = $errorNbr + \count($errors[$page->getId()]);
            }

            if ($errorNbr > 100) {
                break;
            }
        }

        return $this->render('@PiedWebCMS/admin/page_scanView.html.twig', [
            'errorsByPages' => $errors,
        ]);
    }
}
