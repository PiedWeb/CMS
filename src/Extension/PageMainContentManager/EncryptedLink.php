<?php

namespace PiedWeb\CMSBundle\Extension\PageMainContentManager;

class EncryptedLink
{
    public static function encrypt($path)
    {
        if (0 === strpos($path, 'http://')) {
            $path = '-'.substr($path, 7);
        } elseif (0 === strpos($path, 'https://')) {
            $path = '_'.substr($path, 8);
        } elseif (0 === strpos($path, 'mailto:')) {
            $path = '@'.substr($path, 7);
        }

        return str_rot13($path);
    }
}
