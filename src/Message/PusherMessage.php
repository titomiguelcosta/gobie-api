<?php

namespace App\Message;

final class PusherMessage
{
    private $channel;
    private $event;
    private $data;

    public function __construct(string $channel, string $event, array $data = [])
    {
        $this->channel = $channel;
        $this->event = $event;
        $this->data = $data;
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
