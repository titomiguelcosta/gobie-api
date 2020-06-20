<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *  attributes = {
 *      "access_control" = "is_granted('IS_AUTHENTICATED_FULLY')",
 *      "pagination_items_per_page" = 5
 *  },
 *  normalizationContext = {"groups" = {"project"}},
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
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getCreatedBy())"
 *      },
 *      "delete" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getCreatedBy())"
 *      },
 *      "put" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getCreatedBy())"
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
     * @Groups({"job", "project"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Url(
     *  protocols={"http", "https", "git"}
     * )
     * @Groups({"job", "project"})
     */
    private $repo;

    /**
     * @ApiProperty()
     * @Groups({"job", "project"})
     */
    private $repoSanitized;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"job", "project"})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="projects")
     * @Assert\Type(User::class)
     * @Groups({"job", "project"})
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Groups({"job", "project"})
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Job", mappedBy="project", orphanRemoval=true)
     * @Assert\Valid()
     * @Groups({"project"})
     */
    private $jobs;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"job", "project"})
     */
    private $isPrivate;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
        $this->isPrivate = false;
        $this->repoSanitized = $this->getSanitizedRepo();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepo(): ?string
    {
        return $this->repo;
    }

    /**
     * remove username and password from the repo.
     */
    public function getSanitizedRepo(): ?string
    {
        return preg_replace('/.*:?.*@/', '', $this->repo);
    }

    public function setRepo(string $repo): self
    {
        $this->repo = $repo;
        $this->repoSanitized = $this->getSanitizedRepo();

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? $this->getSanitizedRepo();
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

    public function getIsPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $private): self
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
     * @return Job[]
     */
    public function getJobs(): array
    {
        return $this->jobs->getValues();
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
