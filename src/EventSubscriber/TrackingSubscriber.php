<?php

namespace App\EventSubscriber;

use App\Message\Tracking;
use DateTimeImmutable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class TrackingSubscriber implements EventSubscriberInterface
{
    private $bus;
    private $security;
    private $startedAt;

    public function __construct(MessageBusInterface $bus, Security $security)
    {
        $this->bus = $bus;
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $this->startedAt = new DateTimeImmutable();
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        $message = new Tracking(
            $event->getRequest(),
            $event->getResponse(),
            $this->startedAt,
            new DateTimeImmutable(),
            $this->security->getUser()
        );

        $this->bus->dispatch($message);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.terminate' => 'onKernelTerminate',
            'kernel.request' => 'onKernelRequest',
        ];
    }
}
