<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TrackingMessage
{
    private $routeName;
    private $queryParameters;
    private $requestHeaders;
    private $requestMethod;
    private $requestFormat;
    private $requestBody;
    private $requestPathInfo;
    private $ipAddress;
    private $locale;
    private $device;
    private $navigator;
    private $responseBody;
    private $responseStatusCode;
    private $responseStatusText;
    private $startedAt;
    private $terminatedAt;
    private $userId;

    public function __construct(
        Request $request,
        Response $response,
        \DateTimeInterface $startedAt,
        \DateTimeInterface $terminatedAt,
        ?User $user
    ) {
        $this->routeName = $request->attributes->get('_route', 'unknown');
        $this->queryParameters = $request->query->all();
        $this->requestHeaders = $request->headers->all();
        $this->requestMethod = $request->getRealMethod();
        $this->requestFormat = $request->getRequestFormat();
        $this->requestBody = $request->getContent();
        $this->requestPathInfo = $request->getPathInfo();
        $this->ipAddress = $request->getClientIp();
        $this->locale = $request->getLocale();
        $this->device = '';
        $this->navigator = $request->headers->get('User-Agent');
        $this->responseBody = ''; // Having issues when body is too big
        $this->responseStatusCode = $response->getStatusCode();
        $this->responseStatusText = Response::$statusTexts[$response->getStatusCode()] ?? 'unknown status';
        $this->startedAt = $startedAt;
        $this->terminatedAt = $terminatedAt;
        $this->userId = $user instanceof User ? $user->getId() : null;

        $this->sanitize();
    }

    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getTerminatedAt(): \DateTimeInterface
    {
        return $this->terminatedAt;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }

    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function getRequestFormat(): string
    {
        return $this->requestFormat;
    }

    public function getRequestBody(): ?string
    {
        return $this->requestBody;
    }

    public function getRequestPathInfo(): string
    {
        return $this->requestPathInfo;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function getNavigator(): ?string
    {
        return $this->navigator;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    public function getResponseStatusCode(): int
    {
        return $this->responseStatusCode;
    }

    public function getResponseStatusText(): string
    {
        return $this->responseStatusText;
    }

    private function sanitize(): void
    {
        if (isset($this->requestHeaders['authorization'])) {
            unset($this->requestHeaders['authorization']);
        }
    }
}
