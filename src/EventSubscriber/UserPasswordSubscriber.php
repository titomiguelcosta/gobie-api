<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

final class UserPasswordSubscriber implements EventSubscriber
{
    private $encoder;

    public function __construct(
        UserPasswordEncoderInterface $encoder
    ) {
        $this->encoder = $encoder;
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        /** @var User $user */
        $user = $event->getObject();

        if (false === $user instanceof User) {
            return;
        }

        $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        /** @var User $user */
        $user = $event->getObject();

        if (false === $user instanceof User) {
            return;
        }

        if ($user->getPlainPassword()) {
            $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));
        }
    }


    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }
}
