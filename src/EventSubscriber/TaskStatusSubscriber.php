<?php

namespace App\EventSubscriber;

use App\Entity\Task;
use DH\DoctrineAuditBundle\AuditConfiguration;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use LogicException;
use Symfony\Component\Workflow\StateMachine;

final class TaskStatusSubscriber implements EventSubscriber
{
    private $stateMachine;
    private $auditConfiguration;

    public function __construct(StateMachine $stateMachine, AuditConfiguration $auditConfiguration)
    {
        $this->stateMachine = $stateMachine;
        $this->auditConfiguration = $auditConfiguration;
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $task = $event->getObject();
        if ($task instanceof Task && $event->hasChangedField('status')) {
            if (!in_array($event->getNewValue('status'), [Task::STATUS_SUCCEEDED, Task::STATUS_FAILED, Task::STATUS_ABORTED], true)) {
                $this->auditConfiguration->disableAuditFor(Task::class);
            }

            $transitionName = sprintf('%s_to_%s', $event->getOldValue('status'), $event->getNewValue('status'));
            $task->setStatus($event->getOldValue('status'));
            if ($this->stateMachine->can($task, $transitionName)) {
                $this->stateMachine->apply($task, $transitionName);
            } else {
                throw new LogicException(sprintf('Invalid task status. From %s to %s. %s.', $event->getOldValue('status'), $event->getNewValue('status'), $transitionName));
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
