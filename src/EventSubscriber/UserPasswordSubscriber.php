<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserPasswordSubscriber implements EventSubscriber
{
    public function __construct(private UserPasswordHasherInterface $encoder)
    {
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        /** @var User $user */
        $user = $event->getObject();

        if (false === $user instanceof User) {
            return;
        }

        $user->setPassword($this->encoder->hashPassword($user, $user->getPlainPassword()));
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        /** @var User $user */
        $user = $event->getObject();

        if (false === $user instanceof User) {
            return;
        }

        if ($user->getPlainPassword()) {
            $user->setPassword($this->encoder->hashPassword($user, $user->getPlainPassword()));
        }
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }
}
