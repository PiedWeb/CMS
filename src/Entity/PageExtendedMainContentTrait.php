<?php

namespace PiedWeb\CMSBundle\Entity;

use Michelf\Markdown;

trait PageExtendedMainContentTrait
{
    protected $chapeau;

    protected $readableContent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $mainContentIsMarkdown;

    abstract public function getMainContent(): ?string;

    public static function removeHtmlComments(string $content)
    {
        return preg_replace('/<!--(.|\s)*?-->/', '', $content);
    }

    protected function manageMainContent()
    {
        $content = (string) $this->getMainContent();
        $content = explode('<!--break-->', $content);

        $this->chapeau = isset($content[1]) ? self::removeHtmlComments($content[0]) : null;
        $this->readableContent = self::removeHtmlComments(isset($content[1]) ? $content[1] : $content[0]);

        if ($this->mainContentIsMarkdown) {
            if ($this->chapeau) {
                $this->chapeau = Markdown::defaultTransform($this->chapeau);
            }
            if ($this->readableContent) {
                $this->readableContent = Markdown::defaultTransform($this->readableContent);
            }
        }
    }

    public function getReadableContent()
    {
        if (null === $this->readableContent) {
            $this->manageMainContent();
        }

        return $this->readableContent;
    }

    public function getChapeau()
    {
        if (null === $this->readableContent) {
            $this->manageMainContent();
        }

        return $this->chapeau;
    }

    /**
     * Shortcut, will be destroy soon.
     */
    public function getSubtitle()
    {
        return $this->getEmc('subtitle');
    }

    public function getEmc($name)
    {
        if (preg_match('/<!--"'.$name.'"--(.*)--\/-->/s', $this->getMainContent(), $match)) {
            return $match[1];
        }
    }

    public function mainContentIsMarkdown(): bool
    {
        return null === $this->mainContentIsMarkdown ? false : $this->mainContentIsMarkdown;
    }

    public function setMainContentIsMarkdown(bool $mainContentIsMarkdown): self
    {
        $this->mainContentIsMarkdown = $mainContentIsMarkdown;

        return $this;
    }
}
