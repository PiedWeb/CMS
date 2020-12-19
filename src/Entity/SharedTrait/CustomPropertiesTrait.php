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

    /**
     * Stand Alone for Not Indexed.
     *
     * @var array
     */
    protected $StandAloneCustomPropertiesParsed;

    protected $StandAloneCustomProperties;

    public function getCustomProperties(): ?string
    {
        return $this->customProperties;
    }

    public function setCustomProperties($customProperties): self
    {
        $this->customProperties = $customProperties;
        $this->customPropertiesParsed = null;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validateCustomProperties(ExecutionContextInterface $context, $path = 'customProperties'): void
    {
        if (empty($this->customProperties)) {
            return;
        }

        try {
            $parsed = Yaml::parse($this->customProperties);
            if (isset($parsed['customProperties']) || $parsed['CustomProperties']) {
                $context->buildViolation('page.customProperties.cantUseCustomPropertiesInside')
                        ->atPath($path)
                        ->addViolation();
            }
        } catch (ParseException $exception) {
            $context->buildViolation('page.customProperties.malformed') //'$exception->getMessage())
                    ->atPath($path)
                    ->addViolation();
        }
    }

    /**
     * Return custom properties without the ones wich have a get method.
     */
    public function getStandAloneCustomProperties(): string
    {
        $customPropertiesParsed = $this->getCustomPropertiesParsed();
        if (! $customPropertiesParsed) {
            return '';
        }
        $standStandAloneCustomPropertiesParsed = array_filter(
            $customPropertiesParsed,
            [$this, 'isStandAloneCustomProperty'],
            ARRAY_FILTER_USE_KEY
        );

        return Yaml::dump($standStandAloneCustomPropertiesParsed);
    }

    public function setStandAloneCustomProperties(?string $standStandAloneCustomProperties, $merge = false)
    {
        $this->standAloneCustomProperties = $standStandAloneCustomProperties;

        if ($merge) {
            $this->mergeStandAloneCustomProperties();
        }

        $this->customProperties = Yaml::dump(Yaml::parse($this->customProperties));
        // check them :
        // 1. Can We parse them ?
        // Yes - Remove duplicate (done )
        $this->customPropertiesParsed = null;

        return $this;
    }

    protected function mergeStandAloneCustomProperties()
    {
        $standStandAloneParsed = Yaml::parse($this->standAloneCustomProperties);
        $this->standAloneCustomProperties = null;

        if (! $standStandAloneParsed) {
            return;
        }

        foreach ($standStandAloneParsed as $name => $value) {
            if (! $this->isStandAloneCustomProperty($name)) {
                throw new CustomPropertiesException($name);
            }

            $this->setCustomProperty($name, $value);
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateStandAloneCustomProperties(ExecutionContextInterface $context, $path = 'standAloneCustomProperties'): void
    {
        if (empty($this->standStandAloneCustomProperties)) {
            return;
        }

        try {
            $this->mergeStandAloneCustomProperties();
        } catch (ParseException $exception) {
            $context->buildViolation('page.customProperties.malformed') //'$exception->getMessage())
                    ->atPath($path)
                    ->addViolation();
        } catch (CustomPropertiesException $exception) {
            $context->buildViolation('page.customProperties.notStandAlone') //'$exception->getMessage())
                    ->atPath($path)
                    ->addViolation();
        }

        $this->validateCustomProperties($context, $path); // too much
    }

    protected function isStandAloneCustomProperty($name): bool
    {
        return ! method_exists($this, 'get'.ucfirst($name)) && ! method_exists($this, 'get'.$name);
    }

    public function setCustomProperty($name, $value): self
    {
        $customPropertiesParsed = $this->getCustomPropertiesParsed();

        $customPropertiesParsed[$name] = $value;

        $this->customProperties = Yaml::dump($customPropertiesParsed);

        return $this;
    }

    protected function getCustomPropertiesParsed(): array
    {
        if (null === $this->customPropertiesParsed) {
            $this->customPropertiesParsed = $this->customProperties ? Yaml::parse($this->customProperties) : [];
        }

        return $this->customPropertiesParsed;
    }

    /**
     * @return mixed
     */
    public function getCustomProperty(string $name)
    {
        $customPropertiesParsed = $this->getCustomPropertiesParsed();

        return $customPropertiesParsed[$name] ?? null;
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
