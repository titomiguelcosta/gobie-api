<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\AuthController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *  attributes = {
 *      "normalization_context" = {"groups" = {"read"}},
 *      "denormalization_context" = {"groups" = {"write"}},
 *      "access_control" = "is_granted('IS_AUTHENTICATED_FULLY')"
 *  },
 *  collectionOperations={
 *      "get" = {
 *          "access_control" = "is_granted('ROLE_ADMIN')"
 *      },
 *      "post" = {
 *          "validation_groups" = {"Default", "create"},
 *          "access_control" = "is_granted('ROLE_ADMIN')"
 *      }
 *  },
 *  itemOperations = {
 *      "get" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object)"
 *      },
 *      "delete" = {
 *          "access_control" = "is_granted('ROLE_ADMIN')"
 *      },
 *      "put" = {
 *          "access_control" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object)"
 *      },
 *      "auth" = {
 *          "method" = "POST",
 *          "path" = "/users/auth",
 *          "controller" = AuthController::class,
 *          "defaults" = {"_api_receive" = false},
 *          "access_control" = "is_granted('IS_AUTHENTICATED_ANONYMOUSLY')",
 *          "normalization_context" = {"groups" = {"auth"}},
 *          "swagger_context"={
 *              "parameters" = {
 *                  {
 *                      "name" = "user",
 *                      "in" = "body",
 *                      "description" = "Login details",
 *                      "schema" = {
 *                          "type" = "object",
 *                          "properties" = {
 *                              "username" = {"type" = "string"},
 *                              "password" = {"type" = "string"}
 *                          }
 *                      },
 *                      "example" = {
 *                          "username" = "example",
 *                          "password" = "secret"
 *                      }
 *                  }
 *              },
 *              "responses" = {
 *                  "200" = {
 *                      "description" = "Generate JWT token",
 *                      "schema" =  {
 *                          "type" = "object",
 *                          "required" = {
 *                              "id",
 *                              "username",
 *                              "token",
 *                              "email",
 *                              "roles"
 *                          },
 *                          "properties" = {
 *                              "id" = {
 *                                  "type" = "integer"
 *                              },
 *                              "username" = {
 *                                  "type" = "string"
 *                              },
 *                              "token" = {
 *                                  "type" = "string"
 *                              },
 *                              "email" = {
 *                                  "type" = "string"
 *                              },
 *                             "roles" = {
 *                                  "type" = "object"
 *                              }
 *                          }
 *                      }
 *                  },
 *                  "400" = {
 *                      "description" = "Invalid input"
 *                  },
 *                  "404" = {
 *                      "description" = "User not found"
 *                  }
 *              },
 *              "summary" = "Obtains an JWT token",
 *              "consumes" = {
 *                  "application/ld+json",
 *                  "application/json",
 *              },
 *              "produces" = {
 *                  "application/json"
 *              }
 *          }
 *      }
 *  })
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ApiProperty(identifier=false)
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read", "job", "project"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email()
     * @Groups({"read", "write"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"read", "write"})
     */
    private $roles = [];

    /**
     * @ApiProperty(identifier=true)
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Regex("/^\w+$/")
     * @Groups({"auth", "read", "write", "job", "project"})
     */
    private $username;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Groups({"auth", "read"})
     */
    private $password;

    /**
     * @var string The plain text password
     * @Assert\NotBlank(groups={"create"})
     * @Groups({"write"})
     */
    private $plainPassword;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="createdBy")
     */
    private $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
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
        return $this->username;
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
    public function getPassword(): string
    {
        return (string) $this->password;
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
}
