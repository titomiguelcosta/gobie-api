<?php

namespace App\EventSubscriber;

use App\AWS\BatchService;
use App\Entity\Job;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Swift_Mailer;
use Swift_Message;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

final class JobCreatedSubscriber implements EventSubscriber
{
    private $batchService;
    private $mailer;

    public function __construct(
        BatchService $batchService,
        Swift_Mailer $mailer
    ) {
        $this->batchService = $batchService;
        $this->mailer = $mailer;
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $job = $event->getObject();

        if (false === $job instanceof Job) {
            return;
        }

        $this->batchService->submitJob($job);
        $this->doEmail($job);
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
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
