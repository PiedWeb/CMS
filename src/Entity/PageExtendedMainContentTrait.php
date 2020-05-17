<?php

namespace PiedWeb\CMSBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

trait PageExtendedMainContentTrait
{
    protected $chapeau;

    protected $readableContent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $mainContentIsMarkdown;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $otherProperties;

    protected $otherPropertiesParsed;

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
        $this->readableContent = \PiedWeb\CMSBundle\Twig\AppExtension::convertMarkdownImage(
            self::removeHtmlComments(isset($content[1]) ? $content[1] : $content[0])
        );

        if ($this->mainContentIsMarkdown) {
            if ($this->chapeau) {
                $this->chapeau = '{% filter markdown %}'.$this->chapeau.'{% endfilter %}';
            }
            if ($this->readableContent) {
                $this->readableContent = '{% filter markdown %}'.$this->readableContent.'{% endfilter %}';
            }
        }
    }

    public function getReadableContent()
    {
        $this->manageMainContent();
        /* Disable cache because it's generate an error with StaticBundle
        if (null === $this->readableContent) {
            $this->manageMainContent();
        }
        /**/

        return $this->readableContent;
    }

    public function getChapeau()
    {
        $this->manageMainContent();

        return $this->chapeau;
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

    public function getTemplate(): ?string
    {
        return $this->getOtherProperty('template');
    }

    public function getOtherProperties()
    {
        return $this->otherProperties;
    }

    public function setOtherProperties($otherProperties)
    {
        $this->otherProperties = $otherProperties;
        $this->otherPropertiesParsed = null;

        return $otherProperties;
    }

    /**
     * @Assert\Callback
     */
    public function validateOtherProperties(ExecutionContextInterface $context)
    {
        if (!empty($this->otherProperties)) {
            // ou utiliser yaml_parse
            try {
                Yaml::parse($this->otherProperties);
            } catch (ParseException $exception) {
                $context->buildViolation('page.otherProperties.malformed') //'$exception->getMessage())
                    ->atPath('otherProperties')
                    ->addViolation();
            }
        }
    }

    protected function getOtherProperty($name)
    {
        if (null === $this->otherPropertiesParsed) {
            $this->otherPropertiesParsed = $this->otherProperties ? Yaml::parse($this->otherProperties) : [];
        }

        return $this->otherPropertiesParsed[$name] ?? null;
        /*
        if (!isset($this->otherPropertiesParsed[$name])) {
            throw new \Exception('"'.$name.'" is not defined.');
        }

        return $this->otherPropertiesParsed[$name];
        /**/
    }

    /**
     * Magic getter for otherProperties.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if ('_action' == $method) {
            return; // avoid error with sonata
        }

        if (preg_match('/^get/', $method)) {
            $property = lcfirst(preg_replace('/^get/', '', $method));
            if (!property_exists(get_class($this), $property)) {
                return $this->getOtherProperty($property) ?? $this->getEmc($property);
                // todo remove the else next release
            }

            return $this->$property;
        } else {
            $vars = array_keys(get_object_vars($this));
            if (in_array($method, $vars)) {
                return call_user_func_array([$this, 'get'.ucfirst($method)], $arguments);
            }

            return $this->getOtherProperty(lcfirst($method)) ?? $this->getEmc($method);
        }
    }

    // To remove next release
    public function getEmc($name)
    {
        if (preg_match('/<!--"'.$name.'"--(.*)--\/-->/sU', $this->getMainContent(), $match)) {
            return $match[1];
        }
    }
}
