<?php

namespace App\Message;

use App\Entity\User;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Tracking
{
    private $request;
    private $response;
    private $startedAt;
    private $terminatedAt;
    private $userId;

    public function __construct(
        Request $request,
        Response $response,
        DateTimeInterface $startedAt,
        DateTimeInterface $terminatedAt,
        ?User $user
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->startedAt = $startedAt;
        $this->terminatedAt = $terminatedAt;
        $this->userId = $user instanceof User ? $user->getId() : null;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getStartedAt(): DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getTerminatedAt(): DateTimeInterface
    {
        return $this->terminatedAt;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
}
