<?php

namespace App\EventSubscriber;

use LogicException;
use Doctrine\Common\EventSubscriber;
use App\Entity\Task;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Workflow\StateMachine;

final class TaskStatusSubscriber implements EventSubscriber
{
    private $stateMachine;

    public function __construct(StateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $task = $event->getObject();
        if ($task instanceof Task && $event->hasChangedField('status')) {
            $transitionName = sprintf('%s_to_%s', $event->getOldValue('status'), $event->getNewValue('status'));
            
            $task->setStatus($event->getOldValue('status'));
            if ($this->stateMachine->can($task, $transitionName)) {
                $this->stateMachine->apply($task, $transitionName);
            } else {
                throw new LogicException(
                    sprintf('Invalid task status. From %s to %s. %s.', $event->getOldValue('status'), $event->getNewValue('status'), $transitionName)
                );
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }
}
