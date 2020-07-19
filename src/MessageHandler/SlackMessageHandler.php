<?php

namespace App\MessageHandler;

use App\Message\SlackMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

final class SlackMessageHandler implements MessageHandlerInterface
{
    private $notifier;

    public function __construct(ChatterInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function __invoke(SlackMessage $message)
    {
        $chat = new ChatMessage($message->getMessage());
        $chat->transport('slack');

        $this->notifier->send($chat);
    }
}
