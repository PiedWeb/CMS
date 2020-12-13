<?php

namespace PiedWeb\CMSBundle\Extension\PageScanner;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PageScannerController extends AbstractController
{
    /**
     * @var PageScannerService
     */
    protected $scanner;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ContainerBag
     */
    protected $params;

    public function __construct(PageScannerService $scanner, ParameterBagInterface $params)
    {
        $this->scanner = $scanner;
        $this->params = $params;
    }

    public function scanAction()
    {
        $pages = $this->getDoctrine()
            ->getRepository($this->params->get('pwc.entity_page'))
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

        return $this->render('@pwcPageScanner/results.html.twig', [
            'errorsByPages' => $errors,
        ]);
    }
}
