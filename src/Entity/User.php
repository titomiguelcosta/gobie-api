<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\AuthController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    attributes: [
        'normalization_context' => ['groups' => ['read']],
        'denormalization_context' => ['groups' => ['write']],
        'security' => "is_granted('IS_AUTHENTICATED_FULLY')",
    ],
    collectionOperations: [
        'get' => [
            'security' => "is_granted('ROLE_ADMIN')",
        ],
        'post' => [
            'validation_groups' => ['Default', 'create'],
            'security' => "is_granted('ROLE_ADMIN')",
        ],
        'auth' => [
            'method' => 'POST',
            'path' => '/users/auth',
            'controller' => AuthController::class,
            'defaults' => ['_api_receive' => false],
            'security' => "is_granted('PUBLIC_ACCESS')",
            'normalization_context' => [],
            'openapi_context' => [
                'summary' => 'Obtains an JWT token',
                'description' => 'Authenticate user and get access token in the response',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'username' => ['type' => 'string'],
                                    'password' => ['type' => 'string'],
                                ],
                            ],
                            'example' => [
                                'username' => 'example',
                                'password' => 'secret',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Generate JWT token',
                        'schema' => [
                            'type' => 'object',
                            'required' => [
                                'id',
                                'username',
                                'token',
                                'email',
                                'roles',
                            ],
                            'properties' => [
                                '@id' => [
                                    'type' => 'string',
                                ],
                                'id' => [
                                    'type' => 'integer',
                                ],
                                'username' => [
                                    'type' => 'string',
                                ],
                                'token' => [
                                    'type' => 'string',
                                ],
                                'email' => [
                                    'type' => 'string',
                                ],
                                'roles' => [
                                    'type' => 'object',
                                ],
                            ],
                        ],
                    ],
                    '400' => [
                        'description' => 'Invalid input',
                    ],
                    '404' => [
                        'description' => 'User not found',
                    ],
                ],
                'consumes' => [
                    'application/ld+json',
                    'application/json',
                ],
                'produces' => [
                    'application/json',
                ],
            ],
        ],
    ],
    itemOperations: [
        'get' => [
            'security' => "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object)",
        ],
        'delete' => [
            'security' => "is_granted('ROLE_ADMIN')",
        ],
        'put' => [
            'security' => "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object)",
        ],
    ]
)]
#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ApiProperty(identifier=false)
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read', 'job', 'project'])]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\Email]
    #[Groups(['read', 'write'])]
    private $email;

    #[ORM\Column(type: 'json')]
    #[Groups(['read', 'write'])]
    private $roles = [];

    /**
     * @ApiProperty(identifier=true)
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex('/^\w+$/')]
    #[Groups(['auth', 'read', 'write', 'job', 'project'])]
    private $username;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    #[Groups(['auth', 'read'])]
    private $password;

    /**
     * @var string The plain text password
     */
    #[Assert\NotBlank(groups: ['create'])]
    #[Groups(['write'])]
    private $plainPassword;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Project', mappedBy: 'createdBy')]
    private $projects;

    #[ORM\OneToMany(targetEntity: Tracking::class, mappedBy: 'user')]
    private $trackings;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'user')]
    private $events;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->trackings = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): self
    {
        $this->plainPassword = $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setCreatedBy($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getCreatedBy() === $this) {
                $project->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * @return Collection|Tracking[]
     */
    public function getTrackings(): Collection
    {
        return $this->trackings;
    }

    public function addTracking(Tracking $tracking): self
    {
        if (!$this->trackings->contains($tracking)) {
            $this->trackings[] = $tracking;
            $tracking->setUser($this);
        }

        return $this;
    }

    public function removeTracking(Tracking $tracking): self
    {
        if ($this->trackings->contains($tracking)) {
            $this->trackings->removeElement($tracking);
            // set the owning side to null (unless already changed)
            if ($tracking->getUser() === $this) {
                $tracking->setUser(null);
            }
        }

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles, true);
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setUser($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
