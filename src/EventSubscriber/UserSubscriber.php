<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Swift_Mailer;
use App\Entity\User;

final class UserSubscriber implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(
        Swift_Mailer $mailer
    ) {
        $this->mailer = $mailer;
    }

    public function welcomeEmail(GetResponseForControllerResultEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (false === $user instanceof User || Request::METHOD_POST !== $method) {
            return;
        }

        $message = (new \Swift_Message('Grooming Chimps: Welcome'))
            ->setFrom('groomingchimps@titomiguelcosta.com')
            ->setTo($user->getEmail())
            ->setBody(
                sprintf('Hello, welcome to Grooming Chimps. Thanks for joining.'),
                'text/plain'
            );

        $this->mailer->send($message);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                ['welcomeEmail', EventPriorities::PRE_WRITE],
            ],
        ];
    }
}
