<?php

namespace App\EventSubscriber;

use App\Aws\BatchService;
use App\Entity\Job;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;

final class JobRerunSubscriber implements EventSubscriberInterface
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

    public function jobRerun(CompletedEvent $event)
    {
        $this->batchService->submitJob($event->getSubject());
        $this->doEmail($event->getSubject());
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.job.completed.finished_to_pending' => ['jobRerun'],
            'workflow.job.completed.aborted_to_pending' => ['jobRerun'],
        ];
    }

    private function doEmail(Job $job): void
    {
        $message = (new Swift_Message('Grooming Chimps: Job about to rerun'))
            ->setFrom('groomingchimps@titomiguelcosta.com')
            ->setTo('titomiguelcosta@gmail.com')
            ->setBody(
                sprintf('Job #%d submitted to AWS Batch.', $job->getId()),
                'text/plain'
            );

        $this->mailer->send($message);
    }
}
