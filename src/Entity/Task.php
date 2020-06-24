<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *  attributes = {
 *      "access_control" = "is_granted('IS_AUTHENTICATED_FULLY')"
 *  },
 *  collectionOperations={
 *      "get" = {
 *          "access_control" = "is_granted('ROLE_USER')"
 *      },
 *      "post" = {
 *          "access_control" = "is_granted('ROLE_USER')"
 *      }
 *  },
 *  itemOperations = {
 *      "get" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getJob().getProject().getCreatedBy())"
 *      },
 *      "delete" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getJob().getProject().getCreatedBy())"
 *      },
 *      "put" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getJob().getProject().getCreatedBy())"
 *      }
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_ABORTED = 'aborted';
    const STATUS_FAILED = 'failed';
    const STATUS_SUCCEEDED = 'succeeded';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"job", "project"})
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
     * @Groups({"job", "project"})
     */
    private $tool;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"job", "project"})
     */
    private $options;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"job", "project"})
     */
    private $command;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"job", "project"})
     */
    private $output;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"job", "project"})
     */
    private $errorOutput;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"job", "project"})
     */
    private $graph;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"job", "project"})
     */
    private $exitCode;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(\DateTimeInterface::class)
     * @Groups({"job", "project"})
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(\DateTimeInterface::class)
     * @Groups({"job", "project"})
     */
    private $finishedAt;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"job", "project"})
     */
    private $status;

    public function __construct()
    {
        $this->status = self::STATUS_PENDING;
        $this->graph = [];
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

    public function setOptions(array $options = null): self
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

    public function getMarking(): string
    {
        return $this->status;
    }

    public function setMarking(string $status): self
    {
        $this->status = $status;

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

    public function getErrorOutput(): string
    {
        return (string) $this->errorOutput;
    }

    public function setErrorOutput(string $errorOutput = null): self
    {
        // we will have to recalculate field value
        $this->setGraph(null);
        $this->errorOutput = $errorOutput;

        return $this;
    }

    public function getOutput(): string
    {
        return (string) $this->output;
    }

    public function setOutput(string $output = null): self
    {
        // we will have to recalculate field value
        $this->setGraph(null);
        $this->output = $output;

        return $this;
    }

    public function getGraph()
    {
        return $this->graph;
    }

    public function setGraph(array $data = null): self
    {
        $this->graph = $data;

        return $this;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function setExitCode(int $exitCode): self
    {
        $this->exitCode = $exitCode;

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

    public function isSuccessful()
    {
        return self::STATUS_SUCCEEDED === $this->getStatus();
    }

    public function __toString()
    {
        return (string) $this->getTool();
    }
}
