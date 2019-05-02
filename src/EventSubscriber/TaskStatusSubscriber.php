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
            $transitionName = sprintf('%s_to_%s', $event->getOldValue('state'), $event->getNewValue('state'));
            
            if ($this->stateMachine->can($task, $transitionName)) {
                $task->setStatus($event->getOldValue('state'));
                $this->stateMachine->apply($task, $transitionName);
            } else {
                throw new LogicException('Invalid status');
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
