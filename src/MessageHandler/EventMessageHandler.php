<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Event;
use App\Entity\User;
use App\Message\EventMessage;
use Doctrine\ORM\EntityManagerInterface;
use Pusher\Pusher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class EventMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private Pusher $pusher,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(EventMessage $message)
    {
        $user = $this
            ->entityManager
            ->getRepository(User::class)
            ->find((int) $message->getUserId());

        $event = new Event();
        $event->setUser($user);
        $event->setDispatchedAt(new \DateTime());
        $event->setName($message->getName());
        $event->setEntityNamespace($message->getEntityNamespace());
        $event->setEntityId($message->getEntityId());
        $event->setMessage($message->getMessage());
        $event->setLevel($message->getLevel());
        $event->setAction($message->getAction());

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch($message, 'gobie.event.created');

        if ($user instanceof User) {
            $this->pusher->trigger(
                'gobie.event.user.'.$user->getId(),
                'created',
                $message->getMessage()
            );

            $this->eventDispatcher->dispatch($message, 'gobie.event.user.created');
        } else {
            $this->pusher->trigger('event', 'created', $message->getMessage());
        }
    }
}
