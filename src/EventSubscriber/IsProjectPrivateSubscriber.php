<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use App\Entity\Project;
use Doctrine\ORM\Events;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

final class IsProjectPrivateSubscriber implements EventSubscriber
{
    public function prePersist(LifecycleEventArgs $event)
    {
        /** @var Project $project */
        $project = $event->getObject();

        if (false === $project instanceof Project) {
            return;
        }

        $this->isPrivate($project);
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        /** @var Project $project */
        $project = $event->getObject();

        if (false === $project instanceof Project) {
            return;
        }

        $this->isPrivate($project);
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * Private repos should be in the following format
     * https://username:password@github.com/path/repo.git
     */
    private function isPrivate(Project $project): void
    {
        $project->setIsPrivate(false !== strpos($project->getRepo(), '@'));
    }
}
