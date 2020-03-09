<?php

namespace PiedWeb\CMSBundle\Controller;

use PiedWeb\CMSBundle\Service\PageScannerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PageScannerController extends AbstractController
{
    public function __construct(PageScannerService $scanner)
    {
        $this->scanner = $scanner;
    }

    public function scanAction()
    {
        $pages = $this->getDoctrine()
            ->getRepository($this->container->getParameter('app.entity_page'))
            ->findAll();

        $errors = [];

        foreach ($pages as $page) {
            // todo import scanner via setScanner + services.yaml
            $scan = $this->scanner->scan($page);
            if (true !== $scan) {
                $errors = array_merge($errors, $scan);
            }
        }

        return $this->render('@PiedWebCMS/admin/scanView.html.twig', [
            'errors' => $errors,
        ]);
    }
}
