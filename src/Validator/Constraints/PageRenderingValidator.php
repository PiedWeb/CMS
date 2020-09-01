<?php

namespace PiedWeb\CMSBundle\Validator\Constraints;

use PiedWeb\CMSBundle\Service\AppConfigHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PageRenderingValidator extends ConstraintValidator
{
    private $apps;
    private $twig;

    public function __construct(array $apps, $twig)
    {
        $this->twig = $twig;
        $this->apps = $apps;
    }

    public function validate($page, Constraint $constraint)
    {
        if (!$constraint instanceof PageRendering) {
            throw new UnexpectedTypeException($constraint, PageRendering::class);
        }

        if (false !== $page->getRedirection()) { // si c'est une redir, on check rien
            return;
        }

        $template = AppConfigHelper::get($page->getHost(), $this->apps)->getDefaultTemplate();

        try {
            $this->twig->render($template, ['page' => $page]);
        } catch (\Exception $exception) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
            $this->context->buildViolation($exception->getMessage())
                ->addViolation();
        }
    }
}
