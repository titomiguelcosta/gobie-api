<?php

namespace App\EventSubscriber;

use App\Entity\Job;
use Doctrine\Common\EventSubscriber;
use Swift_Mailer;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\WorkflowEvents;

final class JobFinishedSubscriber implements EventSubscriber
{
    private $mailer;

    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function jobFinished(CompletedEvent $event)
    {
        $job = $event->getSubject();
        if ($job instanceof Job && $event->getTransition()->getName() === 'finished') {
            $user = $job->getProject()->getCreatedBy();
            $message = (new \Swift_Message('Grooming Chimps: Job finished'))
                ->setFrom('groomingchimps@titomiguelcosta.com')
                ->setTo($user->getEmail())
                ->setBody(
                    sprintf(
                        'Job #%d finished. Check the report %s.',
                        $job->getId(),
                        'https://groomingchimps.titomiguelcosta.com/jobs/' . $job->getId()
                    ),
                    'text/plain'
                );

            $this->mailer->send($message);
        }
    }

    public function getSubscribedEvents()
    {
        return [
            WorkflowEvents::COMPLETED => ['jobFinished'],
        ];
    }
}
