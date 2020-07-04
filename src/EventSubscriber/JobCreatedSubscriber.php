<?php

namespace App\EventSubscriber;

use App\Entity\Job;
use Aws\Batch\BatchClient;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

final class JobStartSubscriber implements EventSubscriberInterface, EventSubscriber
{
    private $batchClient;
    private $mailer;
    private $tokenManager;

    public function __construct(
        BatchClient $batchClient,
        Swift_Mailer $mailer,
        JWTTokenManagerInterface $tokenManager
    ) {
        $this->batchClient = $batchClient;
        $this->mailer = $mailer;
        $this->tokenManager = $tokenManager;
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $job = $event->getObject();

        if (false !== $job instanceof Job) {
            return;
        }

        $this->doSubmitJob($job);
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

    private function doSubmitJob(Job $job): void
    {
        $user = $job->getProject()->getCreatedBy();
        $token = $this->tokenManager->create($user);
        $username = $user->getUsername();

        $this->batchClient->submitJob([
            'containerOverrides' => [
                'command' => ['app:job:run', $job->getId()],
                'environment' => [
                    [
                        'name' => 'GROOMING_CHIMPS_API_JOB_ID',
                        'value' => $job->getId(),
                    ],
                    [
                        'name' => 'GROOMING_CHIMPS_API_AUTH_TOKEN',
                        'value' => $token,
                    ],
                    [
                        'name' => 'GROOMING_CHIMPS_API_USER_USERNAME',
                        'value' => $username,
                    ],
                ],
            ],
            'jobDefinition' => $_ENV['AWS_BATCH_JOB_DEFINITION_' . $job->getEnvironment()],
            'jobName' => 'api',
            'jobQueue' => $_ENV['AWS_BATCH_JOB_QUEUE_' . $job->getEnvironment()],
        ]);
    }
}
