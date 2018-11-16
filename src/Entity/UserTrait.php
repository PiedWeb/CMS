<?php

namespace PiedWeb\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait UserTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * Loaded from BaseUser.
     *
     * @Assert\Email(
     *     message = "user.email.invalid"
     *     checkMX = true
     * )
     */
    protected $email;

    /**
     * Loaded From BaseUser.
     *
     * @Assert\Length(
     *     min=7,
     *     max=100,
     *     minMessage="user.password.short"
     * )
     */
    protected $plainPassword;

    /**
     * @ORM\PrePersist
     */
    public function overrideUsernameWithEmail()
    {
        $this->username = $this->email;
    }

    /**
     * @ORM\PrePersist
     */
    public function updatedTimestamps(): self
    {
        $this->setCreatedAt(new \DateTime('now'));

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
