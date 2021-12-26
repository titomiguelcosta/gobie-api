<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SlackMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

final class SlackMessageHandler implements MessageHandlerInterface
{
    public function __construct(private ChatterInterface $notifier)
    {
    }

    public function __invoke(SlackMessage $message)
    {
        $chat = new ChatMessage($message->getMessage());
        $chat->transport($message->getChannel());

        $this->notifier->send($chat);
    }
}
