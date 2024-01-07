<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Project;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

final class IsProjectPrivateSubscriber implements EventSubscriber
{
    public function prePersist(PrePersistEventArgs $event): void
    {
        /** @var Project $project */
        $project = $event->getObject();

        if (false === $project instanceof Project) {
            return;
        }

        $this->isPrivate($project);
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        /** @var Project $project */
        $project = $event->getObject();

        if (false === $project instanceof Project) {
            return;
        }

        $this->isPrivate($project);
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * Private repos should be in the following format
     * https://username:password@github.com/path/repo.git.
     */
    private function isPrivate(Project $project): void
    {
        $project->setIsPrivate(false !== strpos($project->getRepo(), '@'));
    }
}
