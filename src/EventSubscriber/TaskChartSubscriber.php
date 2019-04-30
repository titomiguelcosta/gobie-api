<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use App\Entity\Task;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Graph\GraphManager;
use App\Graph\GraphInterface;

final class TaskChartSubscriber implements EventSubscriber
{
    private $graphManager;

    public function __construct(GraphManager $graphManager)
    {
        $this->graphManager = $graphManager;
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
        if ($this->handleEvent($event)) {
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
