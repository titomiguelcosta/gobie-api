<?php

namespace App\Message;

final class SlackMessage
{
    private $message;
    private $channel;

    public function __construct(string $message, string $channel)
    {
        $this->message = $message;
        $this->channel = 'slack_' . $channel;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }
}
