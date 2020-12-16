<?php

namespace PiedWeb\CMSBundle\Service\PageMainContentManager;

/**
 * The name is not amazing... but what else ?!
 * Default is protected kw.
 */
interface MainContentManagerInterface
{
    /**
     * Return text separate by <!-- -->.
     *
     * @return string
     */
    public function getChapeau();

    /**
     * Return rendered content without chapeau (if exist).
     *
     * @return string
     */
    public function getContent();

    public function getBody(bool $withChapeau = false);

    /**
     * All the text before the first title (h2/h3).
     *
     * @return string
     */
    public function getIntro();

    /**
     * Return text separate by <!-- --> (bis).
     *
     * @return string
     */
    public function getPostContent();
}
