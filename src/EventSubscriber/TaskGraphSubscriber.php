<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Task;
use App\Graph\GraphInterface;
use App\Graph\GraphManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

final class TaskGraphSubscriber implements EventSubscriber
{
    public function __construct(private GraphManager $graphManager)
    {
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        if ($this->handleEvent($event)) {
            $task = $event->getObject();
            $task->setGraph($this->recalculateGraph($task));
        }
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        if ($this->handleEvent($event) && $event->hasChangedField('graph')) {
            $event->setNewValue('graph', $this->recalculateGraph($event->getObject()));
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    private function handleEvent(LifecycleEventArgs $event)
    {
        return $event->getObject() instanceof Task;
    }

    private function recalculateGraph(Task $task): array
    {
        $graph = $this->graphManager->getGraph($task);

        $data = [];
        if ($graph instanceof GraphInterface) {
            $data = $graph->getData($task);
        }

        return $data;
    }
}
