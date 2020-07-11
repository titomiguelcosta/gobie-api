<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *  attributes = {
 *      "security" = "is_granted('IS_AUTHENTICATED_FULLY')",
 *      "pagination_items_per_page" = 5
 *  },
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
 *          "security" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getUser())"
 *      },
 *      "delete" = {
 *          "security" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getUser())"
 *      },
 *      "put" = {
 *          "security" = "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user == object.getUser())"
 *      }
 *  }
 * )
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $entityNamespace;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $entityId;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="events")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dispatchedAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $level;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $action;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEntityNamespace(): ?string
    {
        return $this->entityNamespace;
    }

    public function setEntityNamespace(?string $entityNamespace): self
    {
        $this->entityNamespace = $entityNamespace;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDispatchedAt(): ?\DateTimeInterface
    {
        return $this->dispatchedAt;
    }

    public function setDispatchedAt(\DateTimeInterface $dispatchedAt): self
    {
        $this->dispatchedAt = $dispatchedAt;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }
}
