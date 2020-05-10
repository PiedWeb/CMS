<?php

namespace PiedWeb\CMSBundle\Controller;

use PiedWeb\CMSBundle\Service\StaticService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StaticController extends AbstractController
{
    /**
     * @Security("is_granted('ROLE_EDITOR')")
     */
    public function generateStatic(StaticService $static)
    {
        $static->dump();

        return $this->render('@PiedWebCMS/admin/static.html.twig');
    }
}
