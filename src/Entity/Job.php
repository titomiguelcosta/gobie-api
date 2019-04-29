<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *  attributes = {
 *      "access_control" = "is_granted('IS_AUTHENTICATED_FULLY')"
 *  },
 *  normalizationContext = {"groups" = {"job"}},
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
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.project.createdBy)"
 *      },
 *      "delete" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.project.createdBy)"
 *      },
 *      "put" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.project.createdBy)"
 *      }
 *  }
 * )
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

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->branch = 'master';
        $this->status = self::STATUS_PENDING;
        $this->errors = [];
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
}
