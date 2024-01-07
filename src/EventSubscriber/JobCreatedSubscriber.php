<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Aws\BatchService;
use App\Entity\Job;
use App\Message\EventMessage;
use App\Message\PusherMessage;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;

final class JobCreatedSubscriber implements EventSubscriber
{
    public function __construct(
        private BatchService $batchService,
        private MailerInterface $mailer,
        private MessageBusInterface $bus,
        private bool $processJobsEnabled = true
    ) {
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
        $message = (new Email())
            ->subject('Gobie: Job created')
            ->from('gobie@titomiguelcosta.com')
            ->to('titomiguelcosta@gmail.com')
            ->text(sprintf('Job #%d submitted to AWS Batch.', $job->getId()));

        $this->mailer->send($message);
    }
}
