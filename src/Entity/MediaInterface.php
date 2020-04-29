<?php

namespace PiedWeb\CMSBundle\Entity;

interface MediaInterface
{
    public function getWidth();

    public function getMedia();

    public function getRelativeDir();

    public function getSlug();

    public function getPath();

    public function setMainColor();
}
