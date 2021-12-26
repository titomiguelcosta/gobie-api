<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PusherMessage;
use Pusher\Pusher;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PusherMessageHandler implements MessageHandlerInterface
{
    public function __construct(private Pusher $pusher)
    {
    }

    public function __invoke(PusherMessage $message)
    {
        $this->pusher->trigger($message->getChannel(), $message->getEvent(), $message->getData());
    }
}
