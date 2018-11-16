<?php

namespace PiedWeb\CMSBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class FooterController extends ContactController
{
    public function footer(Request $request): Response
    {
        $json = $request->getContent();
        if (!empty($json)) {
            $json = json_decode($json, true); // 2nd param to get as array
        }

        if (isset($json['contact']) && 1 == $json['contact']) {
            $contact = $this->getContactForm()->createView();
        } else {
            $contact = null;
        }

        return $this->render('@PiedWebCMS/page/_footer.html.twig', ['contact' => $contact]);
    }
}
