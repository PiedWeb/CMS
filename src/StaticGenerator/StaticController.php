<?php

namespace PiedWeb\CMSBundle\StaticGenerator;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StaticController extends AbstractController
{
    /**
     * @Security("is_granted('ROLE_EDITOR')")
     */
    public function generateStatic(StaticAppGenerator $staticAppGenerator)
    {
        $staticAppGenerator->generateAll();

        return $this->render('@PiedWebCMS/admin/static.html.twig');
    }
}
