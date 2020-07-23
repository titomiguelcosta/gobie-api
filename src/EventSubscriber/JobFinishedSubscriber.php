<?php

namespace App\EventSubscriber;

use App\Entity\Job;
use App\Entity\User;
use App\Message\EventMessage;
use App\Message\PusherMessage;
use App\Message\SlackMessage;
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

            $this->doEmail($job, $user);

            $this->bus->dispatch(
                new PusherMessage('gobie.job.'.$job->getId(), Job::STATUS_FINISHED, ['job' => $job->getId()])
            );

            $this->bus->dispatch(
                new SlackMessage(
                    sprintf(
                        'Job #%d by %s just finished building.',
                        $job->getId(),
                        $user->getEmail()
                    ),
                    'builds'
                )
            );

            $eventMessage = new EventMessage();
            $eventMessage
                ->setName('job.finished')
                ->setAction(Job::STATUS_FINISHED)
                ->setEntityNamespace(Job::class)
                ->setEntityId($job->getId())
                ->setUserId($user->getId())
                ->setMessage(sprintf('Job #%d finished building.', $job->getId()))
                ->setLevel('info');
            $this->bus->dispatch($eventMessage);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkflowEvents::COMPLETED => ['jobFinished'],
        ];
    }

    private function doEmail(Job $job, User $user): void
    {
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
    }
}
