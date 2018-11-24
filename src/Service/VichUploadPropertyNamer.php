<?php

namespace PiedWeb\CMSBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;

class VichUploadPropertyNamer extends \Vich\UploaderBundle\Naming\PropertyNamer //implements NamerInterface, ConfigurableInterface
{
    /**
     * Guess the extension of the given file.
     *
     * @param UploadedFile $file
     *
     * @return string|null
     */
    private function getExtension(UploadedFile $file): ?string
    {
        var_dump('hello');
        exit;
        $originalName = $file->getClientOriginalName();

        if ($extension = pathinfo($originalName, PATHINFO_EXTENSION)) {
            return strtolower($extension);
        }

        if ($extension = $file->guessExtension()) {
            return strtolower($extension);
        }

        return null;
    }

    public function name($object, PropertyMapping $mapping): string
    {
        return strtolower(parent::name($object, $mapping));
    }
}
