<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\JobRerunController;
use App\Entity\GitHub\CheckRun;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *  attributes = {
 *      "security" = "is_granted('IS_AUTHENTICATED_FULLY')"
 *  },
 *  normalizationContext = {"groups" = {"job"}},
 *  collectionOperations={
 *      "get" = {
 *          "security" = "is_granted('ROLE_USER')"
 *      },
 *      "post" = {
 *          "security" = "is_granted('ROLE_USER')"
 *      }
 *  },
 *  itemOperations = {
 *      "get" = {
 *          "security" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getProject().getCreatedBy())"
 *      },
 *      "delete" = {
 *          "security" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getProject().getCreatedBy())"
 *      },
 *      "put" = {
 *          "security" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getProject().getCreatedBy())"
 *      },
 *      "rerun" = {
 *          "method" = "POST",
 *          "path" = "/jobs/{id}/rerun.{_format}",
 *          "controller" = JobRerunController::class,
 *          "defaults" = {"_api_receive" = false},
 *          "security" = "is_granted('IS_AUTHENTICATED_ANONYMOUSLY') or (is_granted('ROLE_USER') and user == object)"
 *      }
 *  }
 * )
 * @UniqueEntity("token")
 * @ORM\Entity(repositoryClass="App\Repository\JobRepository")
 */
class Job
{
    const STATUS_PENDING = 'pending';
    const STATUS_STARTED = 'started';
    const STATUS_FINISHED = 'finished';
    const STATUS_ABORTED = 'aborted';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"job", "project"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="jobs")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     * @Groups({"job"})
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"job", "project"})
     */
    private $branch;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"job", "project"})
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"job", "project"})
     */
    private $finishedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="job", orphanRemoval=true)
     * @Assert\Valid()
     * @Groups({"job", "project"})
     */
    private $tasks;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\GitHub\CheckRun", mappedBy="job")
     * @Assert\Valid()
     */
    private $checkRun;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"job", "project"})
     */
    private $errors;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"job", "project"})
     */
    private $status;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"job", "project"})
     */
    private $environment;

    /**
     * @ORM\Column(type="string")
     * @Groups({"job", "project"})
     */
    private $token;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"job", "project"})
     */
    private $commitHash;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->branch = 'master';
        $this->status = self::STATUS_PENDING;
        $this->errors = [];
        $this->token = md5(random_bytes(15));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getBranch(): ?string
    {
        return $this->branch;
    }

    public function setBranch(?string $branch): self
    {
        $this->branch = $branch;

        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
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

    public function setEnvironment(string $env): self
    {
        $this->environment = $env;

        return $this;
    }

    public function getEnvironment(): string
    {
        return strtoupper($this->environment);
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return Task[]
     */
    public function getTasks(): array
    {
        return $this->tasks->getValues();
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setJob($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // set the owning side to null (unless already changed)
            if ($task->getJob() === $this) {
                $task->setJob(null);
            }
        }

        return $this;
    }

    public function getCheckRun(): ?CheckRun
    {
        return $this->checkRun;
    }

    public function setCheckRun(?CheckRun $checkRun): self
    {
        $this->checkRun = $checkRun;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getCommitHash(): ?string
    {
        return $this->commitHash;
    }

    public function setCommitHash(?string $commitHash): self
    {
        $this->commitHash = $commitHash;

        return $this;
    }

    public function __toString()
    {
        return '#' . $this->getId();
    }
}
