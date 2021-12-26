<?php

declare(strict_types=1);

namespace App\Message;

final class EmailMessage
{
    public function __construct(private string $to)
    {
    }

    public function getTo(): string
    {
        return $this->to;
    }
}
