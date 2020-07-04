<?php

namespace App\EventSubscriber;

use App\Entity\Job;
use DH\DoctrineAuditBundle\AuditConfiguration;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use LogicException;
use Symfony\Component\Workflow\StateMachine;

final class JobStatusSubscriber implements EventSubscriber
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
        $job = $event->getObject();
        if ($job instanceof Job && $event->hasChangedField('status')) {
            if (!in_array($event->getNewValue('status'), [Job::STATUS_ABORTED, Job::STATUS_FINISHED], true)) {
                $this->auditConfiguration->disableAuditFor(Job::class);
            }

            $transitionName = sprintf('%s_to_%s', $event->getOldValue('status'), $event->getNewValue('status'));
            $job->setStatus($event->getOldValue('status'));
            if ($this->stateMachine->can($job, $transitionName)) {
                $this->stateMachine->apply($job, $transitionName);
            } else {
                throw new LogicException(sprintf('Invalid job status. From %s to %s. %s.', $event->getOldValue('status'), $event->getNewValue('status'), $transitionName));
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
