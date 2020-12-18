<?php

namespace PiedWeb\CMSBundle\Entity;

use Exception;
use PiedWeb\CMSBundle\Extension\Filter\FilterInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

trait PageExtendedMainContentTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $otherProperties;

    protected $otherPropertiesParsed;

    protected $mainContentManager;

    abstract public function getMainContent(): ?string;

    public function getReadableContent()
    {
        throw new Exception('You should use getContent.content');
    }

    public function getChapeau()
    {
        throw new Exception('You should use getContent');
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
        if (! empty($this->otherProperties)) {
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

    public function getOtherProperty($name)
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
     * TODO/IDEA magic setter for otherProperties.
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
            if (! property_exists(static::class, $property)) {
                return $this->getOtherProperty($property) ?? $this->getEmc($property);
                // todo remove the else next release
            }

            return $this->$property;
        } else {
            $vars = array_keys(get_object_vars($this));
            if (\in_array($method, $vars)) {
                return \call_user_func_array([$this, 'get'.ucfirst($method)], $arguments);
            }

            return $this->getOtherProperty(lcfirst($method)) ?? $this->getEmc($method);
        }
    }

    // NEXT MAJOR: remove it
    public function getEmc($name)
    {
        if (preg_match('/<!--"'.$name.'"--(.*)--\/-->/sU', $this->getMainContent(), $match)) {
            return $match[1];
        }
    }

    /**
     * Get the value of mainContentManager.
     */
    public function getContent()
    {
        return $this->mainContentManager;
    }

    /**
     * Set the value of mainContentManager.
     *
     * @return self
     */
    public function setContent(FilterInterface $mainContentManager)
    {
        $this->mainContentManager = $mainContentManager;

        return $this;
    }
}
