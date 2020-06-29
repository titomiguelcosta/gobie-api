<?php

namespace App\MessageHandler;

use App\Message\PusherMessage;
use Pusher\Pusher;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PusherMessageHandler implements MessageHandlerInterface
{
    private $pusher;

    public function __construct(Pusher $pusher)
    {
        $this->pusher = $pusher;
    }

    public function __invoke(PusherMessage $message)
    {
        $this->pusher->trigger($message->getChannel(), $message->getEvent(), $message->getData());
    }
}
