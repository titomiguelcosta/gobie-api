<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Message\TrackingMessage;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class TrackingSubscriber implements EventSubscriberInterface
{
    private $startedAt;

    public function __construct(private MessageBusInterface $bus, private Security $security)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->startedAt = new \DateTimeImmutable();
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $message = new TrackingMessage(
            $event->getRequest(),
            $event->getResponse(),
            $this->startedAt ?? new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            $this->security->getUser()
        );

        $this->bus->dispatch($message);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'onKernelTerminate',
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
