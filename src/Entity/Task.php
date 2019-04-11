<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_FAILED = 'failed';
    const STATUS_SUCCEEDED = 'succeeded';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Job", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Type(Job::class)
     */
    private $job;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $tool;

    /**
     * @ORM\Column(type="json")
     */
    private $options;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $command;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(\DateTimeInterface::class)
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(\DateTimeInterface::class)
     */
    private $finishedAt;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $status;

    public function __construct()
    {
        $this->status = self::STATUS_PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): self
    {
        $this->job = $job;

        return $this;
    }

    public function getTool(): ?string
    {
        return $this->tool;
    }

    public function setTool(string $tool): self
    {
        $this->tool = $tool;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt = null): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $finishedAt = null): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }
}
