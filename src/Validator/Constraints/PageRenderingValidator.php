<?php

namespace PiedWeb\CMSBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PageRenderingValidator extends ConstraintValidator
{
    private $defaultPageTemplate;
    private $twig;

    public function __construct(string $defaultPageTemplate, $twig)
    {
        $this->twig = $twig;
        $this->defaultPageTemplate = $defaultPageTemplate;
    }

    public function validate($page, Constraint $constraint)
    {
        if (!$constraint instanceof PageRendering) {
            throw new UnexpectedTypeException($constraint, PageRendering::class);
        }

        if (false !== $page->getRedirection()) { // si c'est une redir, on check rien
            return;
        }

        $template = null !== $page->getTemplate() ? $page->getTemplate() : $this->defaultPageTemplate;

        try {
            $this->twig->render($template, ['page' => $page]);
        } catch (\Exception $exception) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

/*
    public function preUpdate(Page $page)
    {
         return $this->context->buildViolation('oups')
                ->addViolation();

        // Maybe the page is a redirection
        if (false !== $page->getRedirection()) {
            $this->redirect($page->getRedirection(), $page->getRedirectionCode());
        } else {

        $template = null !== $page->getTemplate() ? $page->getTemplate() : $this->parameterBag->get('app.default_page_template');

        $this->render($template, ['page' => $page]);

        }
    }
