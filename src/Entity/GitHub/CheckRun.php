<?php

namespace App\Entity\GitHub;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Job;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GitHub\CheckRunRepository")
 */
class CheckRun
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
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Job", inversedBy="checkRun")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Type(Job::class)
     */
    private $job;

    /**
     * @ORM\Column(type="string")
     */
    private $username;

    /**
     * @ORM\Column(type="string")
     */
    private $repo;

    /**
     * @ORM\Column(type="string")
     */
    private $checkId;

    /**
     * @ORM\Column(type="string")
     */
    private $instalationId;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
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

    public function setJob(Job $job): self
    {
        $this->job = $job;
        $job->setCheckRun($this);

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRepo(): string
    {
        return $this->repo;
    }

    public function setRepo(string $repo): self
    {
        $this->repo = $repo;

        return $this;
    }

    public function getCheckId(): string
    {
        return $this->checkId;
    }

    public function setCheckId(string $checkId): self
    {
        $this->checkId = $checkId;

        return $this;
    }

    public function getInstalationId(): string
    {
        return $this->instalationId;
    }

    public function setInstalationId(string $instalationId): self
    {
        $this->instalationId = $instalationId;

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

    public function isSuccessful()
    {
        return self::STATUS_SUCCEEDED === $this->getStatus();
    }

    public function __toString()
    {
        return (string) $this->getStatus();
    }
}
