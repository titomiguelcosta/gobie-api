<?php

namespace App\Message;

final class GitHubCheckRunMessage
{
    private $checkId;

    public function __construct(string $checkId)
    {
        $this->checkId = $checkId;
    }

    public function getCheckId(): string
    {
        return $this->checkId;
    }
}
