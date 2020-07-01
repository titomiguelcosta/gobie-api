<?php

namespace App\EventSubscriber;

use App\Entity\Job;
use App\Message\PusherMessage;
use Swift_Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\WorkflowEvents;

final class JobFinishedSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $bus;

    public function __construct(Swift_Mailer $mailer, MessageBusInterface $bus)
    {
        $this->mailer = $mailer;
        $this->bus = $bus;
    }

    public function jobFinished(CompletedEvent $event)
    {
        $job = $event->getSubject();
        if ($job instanceof Job && Job::STATUS_FINISHED === $job->getStatus()) {
            $user = $job->getProject()->getCreatedBy();
            $message = (new \Swift_Message('Grooming Chimps: Job finished'))
                ->setFrom('groomingchimps@titomiguelcosta.com')
                ->setTo($user->getEmail())
                ->setBody(
                    sprintf(
                        'Job #%d finished. Check the report %s.',
                        $job->getId(),
                        'https://groomingchimps.titomiguelcosta.com/jobs/'.$job->getId()
                    ),
                    'text/plain'
                );

            $this->mailer->send($message);

            $this->bus->dispatch(new PusherMessage('job-'.$job->getId(), 'finished', ['job' => $job->getId()]));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkflowEvents::COMPLETED => ['jobFinished'],
        ];
    }
}
