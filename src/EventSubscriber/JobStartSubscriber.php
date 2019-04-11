<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Job;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Aws\Batch\BatchClient;

final class JobStartSubscriber implements EventSubscriberInterface
{
    private $batchClient;

    public function __construct(BatchClient $batchClient)
    {
        $this->batchClient = $batchClient; 
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $job = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (false === $job instanceof Job || Request::METHOD_POST !== $method) {
            return;
        }

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
                        'name' => 'TOKEN',
                        'value' => 'SECRET_TO_CALL_API_BACK',
                    ],
                ],
            ],
            'jobDefinition' => getenv('AWS_BATCH_JOB_DEFINITION'),
            'jobName' => 'api',
            'jobQueue' => getenv('AWS_BATCH_JOB_QUEUE'),
        ]);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::POST_WRITE],
        ];
    }
}
