<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Job;
use DH\Auditor\Provider\Doctrine\Configuration;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Workflow\StateMachine;

final class JobStatusSubscriber implements EventSubscriber
{
    public function __construct(private StateMachine $stateMachine, private Configuration $auditConfiguration)
    {
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
                throw new \LogicException(sprintf('Invalid job status. From %s to %s. %s.', $event->getOldValue('status'), $event->getNewValue('status'), $transitionName));
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
