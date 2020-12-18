<?php

namespace PiedWeb\CMSBundle\Entity\SharedTrait;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

trait CustomPropertiesTrait
{
    /**
     * YAML Format.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $customProperties;

    protected $customPropertiesParsed;

    public function getCustomProperties()
    {
        return $this->customProperties;
    }

    public function setCustomProperties($customProperties)
    {
        $this->customProperties = $customProperties;
        $this->customPropertiesParsed = null;

        return $customProperties;
    }

    /**
     * @Assert\Callback
     */
    public function validateCustomProperties(ExecutionContextInterface $context)
    {
        if (! empty($this->customProperties)) {
            // ou utiliser yaml_parse
            try {
                Yaml::parse($this->customProperties);
            } catch (ParseException $exception) {
                $context->buildViolation('page.customProperties.malformed') //'$exception->getMessage())
                    ->atPath('customProperties')
                    ->addViolation();
            }
        }
    }

    public function getCustomProperty($name)
    {
        if (null === $this->customPropertiesParsed) {
            $this->customPropertiesParsed = $this->customProperties ? Yaml::parse($this->customProperties) : [];
        }

        return $this->customPropertiesParsed[$name] ?? null;
    }

    /**
     * Magic getter for customProperties.
     * TODO/IDEA magic setter for customProperties.
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
                return $this->getCustomProperty($property) ?? null;
                // todo remove the else next release
            }

            return $this->$property;
        } else {
            $vars = array_keys(get_object_vars($this));
            if (\in_array($method, $vars)) {
                return \call_user_func_array([$this, 'get'.ucfirst($method)], $arguments);
            }

            return $this->getCustomProperty(lcfirst($method)) ?? null;
        }
    }
}
