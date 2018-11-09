<?php

namespace PiedWeb\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Serializer\FormErrorSerializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use PiedWeb\CMSBundle\Entity\Contact;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactController extends AbstractController
{
    private $formErrorSerializer;

    private $translator;

    public function __construct(FormErrorSerializer $formErrorSerializer, TranslatorInterface $translator)
    {
        $this->formErrorSerializer = $formErrorSerializer;
        $this->translator = $translator;
    }

    public function getContactForm()
    {
        $contact = new Contact();

        if ($this->getUser()) {
            $contact = new Contact();
            $contact->setFr0m($this->getUser()->getEmail());
            $contact->setName($this->getUser()->getFirstname().' '.$this->getUser()->getLastname());
        }

        $form = $this->createFormBuilder($contact, [], ['action' => $this->generateUrl('piedweb_cms_contact')]);
        if (!$this->getUser()) {
            $form->add('name');
            $form->add('fr0m', EmailType::class);
        }
        $form->add('message', TextareaType::class);

        return $form->getForm();
    }

    public function show(Request $request, ValidatorInterface $validator)
    {
        $form = $this->getContactForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->sendMessage($form->getData());
                return new JsonResponse(
                    [
                        'status' => 'success',
                        'message' => $this->translator->trans('contact.send.success'),
                    ]
                  );
            } else {
                return new JsonResponse(
                    [
                        'status' => 'error',
                        'errors' => $this->formErrorSerializer->convertFormToArray($form),
                    ],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        } else {
            $form->createView();
            return $this->render('page/contact.html.twig', ['contact' => $form]);
        }
    }

    protected function sendMessage(Contact $contact) {
        $message = (new \Swift_Message())
                ->setSubject($this->translator->trans('contact.send.prefix_subject').' '.$contact->getName())
                ->setFrom($this->getParameter('app_email_sender'))
                ->setReplyTo($contact->getFr0m())
                ->setTo($this->getParameter('app_contact_email'))
                ->setBody($this->renderView('contact/sendmail.html.twig', ['message'=> $contact->getMessage()]), 'text/html');
        // $this->get('mailer')->send($message);

        return;
    }
}
