<?php

namespace PiedWeb\CMSBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Contact
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Regex(
     *      pattern="/^[a-z\-0-9\s]+$/i",
     *      message = "contact.name.invalid"
     * )
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "contact.name.short",
     *      maxMessage = "contact.name.long"
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "contact.from.invalid",
     *     checkMX = true
     * )
     */
    protected $fr0m;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 20,
     *      max = 20000,
     *      minMessage = "contact.message.short",
     *      maxMessage = "contact.message.long"
     * )
     */
    protected $message;

    /**
     * Set name.
     *
     * @param string|null
     *
     * @return ContactForm
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set fr0m.
     *
     * @param string|null
     *
     * @return ContactForm
     */
    public function setFr0m($fr0m)
    {
        $this->fr0m = $fr0m;

        return $this;
    }

    /**
     * Get fr0m.
     *
     * @return string|null
     */
    public function getFr0m()
    {
        return $this->fr0m;
    }

    /**
     * Set message.
     *
     * @param string|null
     *
     * @return ContactForm
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }
}
