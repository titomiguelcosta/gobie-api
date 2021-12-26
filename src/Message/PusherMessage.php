<?php

declare(strict_types=1);

namespace App\Message;

final class PusherMessage
{
    public function __construct(private string $channel, private string $event, private array $data = [])
    {
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
