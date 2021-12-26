<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ResponseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResponseRepository::class)
 */
class Response
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Tracking::class, inversedBy="response", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $tracking;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $statusCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $statusText;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTracking(): ?Tracking
    {
        return $this->tracking;
    }

    public function setTracking(Tracking $tracking): self
    {
        $this->tracking = $tracking;

        return $this;
    }

    public function getStatusCode(): ?string
    {
        return $this->statusCode;
    }

    public function setStatusCode(string $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStatusText(): ?string
    {
        return $this->statusText;
    }

    public function setStatusText(string $statusText): self
    {
        $this->statusText = $statusText;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }
}
