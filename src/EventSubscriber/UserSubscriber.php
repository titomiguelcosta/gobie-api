<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class UserSubscriber implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function welcomeEmail(ViewEvent $event)
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

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                ['welcomeEmail', EventPriorities::PRE_WRITE],
            ],
        ];
    }
}
