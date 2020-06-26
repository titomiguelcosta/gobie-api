<?php

namespace App\Message;

final class EmailMessage
{
    private $to;

    public function __construct(string $to)
    {
        $this->to = $to;
    }

    public function getTo(): string
    {
        return $this->to;
    }
}
