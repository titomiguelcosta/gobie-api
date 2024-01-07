<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestRepository::class)]
class Request
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: Tracking::class, inversedBy: 'request', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $tracking;

    #[ORM\Column(type: 'array', nullable: true)]
    private $queryParameters = [];

    #[ORM\Column(type: 'array', nullable: true)]
    private $headers = [];

    #[ORM\Column(type: 'string', length: 255)]
    private $method;

    #[ORM\Column(type: 'string', length: 255)]
    private $format;

    #[ORM\Column(type: 'text', nullable: true)]
    private $body;

    #[ORM\Column(type: 'string', length: 255)]
    private $pathInfo;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $locale;

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

    public function getQueryParameters(): ?array
    {
        return $this->queryParameters;
    }

    public function setQueryParameters(?array $queryParameters): self
    {
        $this->queryParameters = $queryParameters;

        return $this;
    }

    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    public function setHeaders(?array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

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

    public function getPathInfo(): ?string
    {
        return $this->pathInfo;
    }

    public function setPathInfo(string $pathInfo): self
    {
        $this->pathInfo = $pathInfo;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }
}
