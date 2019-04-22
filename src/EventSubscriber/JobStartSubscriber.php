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
use Symfony\Component\Security\Core\Security;

final class JobStartSubscriber implements EventSubscriberInterface
{
    private $batchClient;
    private $mailer;
    private $tokenManager;
    private $security;

    public function __construct(
        BatchClient $batchClient,
        Swift_Mailer $mailer,
        JWTTokenManagerInterface $tokenManager,
        Security $security
    ) {
        $this->batchClient = $batchClient;
        $this->mailer = $mailer;
        $this->tokenManager = $tokenManager;
        $this->security = $security;
    }

    public function startJobOnAwsBatch(GetResponseForControllerResultEvent $event)
    {
        $job = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (false === $job instanceof Job || Request::METHOD_POST !== $method) {
            return;
        }

        $user = $this->security->getUser();
        $token = $user instanceof User ? $this->tokenManager->create($user) : '';
        $username = $user instanceof User ? $user->getUsername() : 'ANONYMOUS';

        $this->batchClient->submitJob([
            'containerOverrides' => [
                'command' => ['app:job:run', $job->getId()],
                'environment' => [
                    [
                        'name' => 'GROOMING_CHIMPS_API_BASE_URI',
                        'value' => 'https://api.groomingchimps.titomiguelcosta.com',
                    ],
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
                ['startJobOnAwsBatch', EventPriorities::POST_WRITE],
                ['emailNotifyingJobStart', EventPriorities::POST_WRITE],
            ],
        ];
    }
}
