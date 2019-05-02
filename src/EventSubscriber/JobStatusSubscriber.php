<?php

namespace App\EventSubscriber;

use LogicException;
use Doctrine\Common\EventSubscriber;
use App\Entity\Job;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Workflow\StateMachine;

final class JobStatusSubscriber implements EventSubscriber
{
    private $stateMachine;

    public function __construct(StateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $job = $event->getObject();
        if ($job instanceof Job && $event->hasChangedField('status')) {
            $transitionName = sprintf('%s_to_%s', $event->getOldValue('status'), $event->getNewValue('status'));
            
            $job->setStatus($event->getOldValue('status'));
            if ($this->stateMachine->can($job, $transitionName)) {
                $this->stateMachine->apply($job, $transitionName);
            } else {
                throw new LogicException(
                    sprintf('Invalid job status. From %s to %s. %s.', $event->getOldValue('status'), $event->getNewValue('status'), $transitionName)
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
