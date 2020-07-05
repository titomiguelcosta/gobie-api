<?php

namespace App\EventSubscriber;

use App\Aws\BatchService;
use App\Entity\Job;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Swift_Mailer;
use Swift_Message;

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

    public function postPersist(LifecycleEventArgs $event)
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
