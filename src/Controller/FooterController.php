<?php

namespace PiedWeb\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FooterController extends AbstractController
{

    public function show(Request $request) :Response
    {
        if ($request->get('contact') == 1) {
            $contact = $this->forward('PiedWeb\CMSBundle\ContactController::getContactForm');

            $this->getContactForm()->createView();
        } else {
            $contact = null;
        }

        return $this->render('page/_footer.html.twig', ['contact' => $contact]);
    }
}
