<?php

namespace App\EventSubscriber;

use App\Aws\BatchService;
use App\Entity\Job;
use App\Message\EventMessage;
use App\Message\PusherMessage;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Messenger\MessageBusInterface;

final class JobCreatedSubscriber implements EventSubscriber
{
    private $batchService;
    private $mailer;
    private $bus;
    private $processJobsEnabled;

    public function __construct(
        BatchService $batchService,
        Swift_Mailer $mailer,
        MessageBusInterface $bus,
        bool $processJobsEnabled = true
    ) {
        $this->batchService = $batchService;
        $this->mailer = $mailer;
        $this->bus = $bus;
        $this->processJobsEnabled = $processJobsEnabled;
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $job = $event->getObject();

        if (false === $job instanceof Job || false === $this->processJobsEnabled) {
            return;
        }

        $this->batchService->submitJob($job);

        $this->bus->dispatch(
            new PusherMessage('gobie.job.'.$job->getId(), Job::STATUS_STARTED, ['job' => $job->getId()])
        );

        $eventMessage = new EventMessage();
        $eventMessage
            ->setName('job.started')
            ->setAction(Job::STATUS_STARTED)
            ->setEntityNamespace(Job::class)
            ->setEntityId($job->getId())
            ->setUserId($job->getProject()->getCreatedBy()->getId())
            ->setMessage(sprintf('Job #%d queued to be built.', $job->getId()))
            ->setLevel('info');
        $this->bus->dispatch($eventMessage);

        $this->doEmail($job);
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    private function doEmail(Job $job): void
    {
        $message = (new Swift_Message('Grooming Chimps: Job created'))
            ->setFrom('groomingchimps@titomiguelcosta.com')
            ->setTo('titomiguelcosta@gmail.com')
            ->setBody(
                sprintf('Job #%d submitted to AWS Batch.', $job->getId()),
                'text/plain'
            );

        $this->mailer->send($message);
    }
}
