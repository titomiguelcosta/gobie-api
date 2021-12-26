<?php

declare(strict_types=1);

namespace App\Aws;

use App\Entity\Job;
use Aws\Batch\BatchClient;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class BatchService
{
    public function __construct(
        private BatchClient $batchClient,
        private JWTTokenManagerInterface $tokenManager
    ) {
    }

    public function submitJob(Job $job): void
    {
        $user = $job->getProject()->getCreatedBy();
        $token = $this->tokenManager->create($user);
        $username = $user->getUsername();

        $this->batchClient->submitJob([
            'containerOverrides' => [
                'command' => ['app:job:run', $job->getId()],
                'environment' => [
                    [
                        'name' => 'GOBIE_API_JOB_ID',
                        'value' => $job->getId(),
                    ],
                    [
                        'name' => 'GOBIE_API_AUTH_TOKEN',
                        'value' => $token,
                    ],
                    [
                        'name' => 'GOBIE_API_USER_USERNAME',
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
