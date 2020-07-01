<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Job;
use Aws\Batch\BatchClient;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Workflow\Event\CompletedEvent;

final class JobStartSubscriber implements EventSubscriberInterface
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

    public function jobCreated(ViewEvent $event)
    {
        $job = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (false === $job instanceof Job || Request::METHOD_POST !== $method) {
            return;
        }

        $this->doSubmitJob($job);
        $this->doEmail($job);
    }

    public function jobRerun(CompletedEvent $event)
    {
        $this->doSubmitJob($event->getSubject());
        $this->doEmail($event->getSubject());
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                ['jobCreated', EventPriorities::POST_WRITE],
            ],
            'workflow.job.completed.finished_to_pending' => ['jobRerun'],
            'workflow.job.completed.aborted_to_pending' => ['jobRerun'],
        ];
    }

    private function doEmail(Job $job): void
    {
        $message = (new Swift_Message('Grooming Chimps: Submit Job to AWS Batch'))
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
