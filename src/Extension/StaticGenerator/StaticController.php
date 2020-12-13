<?php

namespace PiedWeb\CMSBundle\Extension\StaticGenerator;

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

        return $this->render('@pwcStaticGenerator/results.html.twig');
    }
}
