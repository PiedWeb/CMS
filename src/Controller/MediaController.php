<?php

namespace PiedWeb\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use PiedWeb\CMSBundle\Entity\PageInterface as Page;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MediaController extends AbstractController
{
    protected $translator;

    public function download(
        string $path,
        Request $request,
        TranslatorInterface $translator,
        ParameterBagInterface $params
    ) {

        $pathToFile = $this->get('kernel')->getProjectDir().'/media/'.substr(str_replace('..', '', $path), strlen('media/'));

        if (!file_exists($pathToFile)) {
            throw $this->createNotFoundException('The media does not exist...');
        }

        $response = new BinaryFileResponse($pathToFile);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;

    }
}
