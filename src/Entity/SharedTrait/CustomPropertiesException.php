<?php

namespace PiedWeb\CMSBundle\Entity\SharedTrait;

use Exception;

class CustomPropertiesException extends Exception
{
    public function __construct(string $name)
    {
        $message = 'use `setStandAloneCustomProperties` for an index custom property (`'.$name.'`)';

        parent::__construct($message);
    }
}
