<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function welcomeEmail(ViewEvent $event): void
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (false === $user instanceof User || Request::METHOD_POST !== $method) {
            return;
        }

        $message = (new Email())
            ->subject('Gobie: Welcome')
            ->from('gobie@titomiguelcosta.com')
            ->to($user->getEmail())
            ->text(sprintf('Hello, welcome to Gobie. Thanks for joining.'));

        $this->mailer->send($message);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['welcomeEmail', EventPriorities::PRE_WRITE],
            ],
        ];
    }
}
