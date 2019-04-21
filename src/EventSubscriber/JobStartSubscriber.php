<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Job;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Aws\Batch\BatchClient;
use Swift_Mailer;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use function GuzzleHttp\json_encode;

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

    public function startJobOnAwsBatch(GetResponseForControllerResultEvent $event)
    {
        $job = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (false === $job instanceof Job || Request::METHOD_POST !== $method) {
            return;
        }

        $user = $this->security->getUser();
        $this->batchClient->submitJob([
            'containerOverrides' => [
                'command' => ['--version'],
                'environment' => [
                    [
                        'name' => 'APP_ENV',
                        'value' => getenv('APP_ENV'),
                    ],
                    [
                        'name' => 'PROJECT_ID',
                        'value' => $job->getId(),
                    ],
                    [
                        'name' => 'AUTH_TOKEN',
                        'value' => $user instanceof User ? $this->tokenManager->create($user) : '',
                    ],
                    [
                        'name' => 'TASKS',
                        'value' => json_encode($job->getTasksAsArray()),
                    ],
                ],
            ],
            'jobDefinition' => getenv('AWS_BATCH_JOB_DEFINITION'),
            'jobName' => 'api',
            'jobQueue' => getenv('AWS_BATCH_JOB_QUEUE'),
        ]);
    }

    public function emailNotifyingJobStart(GetResponseForControllerResultEvent $event)
    {
        $job = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (false === $job instanceof Job || Request::METHOD_POST !== $method) {
            return;
        }

        $message = (new \Swift_Message('Grooming Chimps: Submit Job to AWS Batch'))
            ->setFrom('groomingchimps@titomiguelcosta.com')
            ->setTo('titomiguelcosta@gmail.com')
            ->setBody(
                sprintf('Job #%d submitted to AWS Batch.', $job->getId()),
                'text/plain'
            );

        $this->mailer->send($message);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                ['startJobOnAwsBatch', 10 + EventPriorities::POST_WRITE],
                ['emailNotifyingJobStart', 5 + EventPriorities::POST_WRITE],
            ],
        ];
    }
}
