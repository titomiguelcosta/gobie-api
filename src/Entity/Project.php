<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *  attributes = {"access_control" = "is_granted('ROLE_USER')"},
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
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.createdBy)"
 *      },
 *      "delete" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.createdBy)"
 *      },
 *      "put" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.createdBy)"
 *      }
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"job"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Url(
     *  protocols={"http", "https", "git"}
     * )
     * @Groups({"job"})
     */
    private $repo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"job"})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="projects")
     * @Assert\Type(User::class)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Groups({"job"})
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Job", mappedBy="project", orphanRemoval=true)
     * @Assert\Valid()
     */
    private $jobs;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPrivate;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
        $this->isPrivate = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepo(): ?string
    {
        return $this->repo;
    }

    public function setRepo(string $repo): self
    {
        $this->repo = $repo;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function setPrivate(bool $private): self
    {
        $this->isPrivate = $private;

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

    /**
     * @return Collection|Job[]
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): self
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs[] = $job;
            $job->setProject($this);
        }

        return $this;
    }

    public function removeJob(Job $job): self
    {
        if ($this->jobs->contains($job)) {
            $this->jobs->removeElement($job);
            // set the owning side to null (unless already changed)
            if ($job->getProject() === $this) {
                $job->setProject(null);
            }
        }

        return $this;
    }
}
