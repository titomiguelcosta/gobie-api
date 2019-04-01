<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Aws\Batch\BatchClient;

class TestAwsBatchJobSubmitCommand extends Command
{
    protected static $defaultName = 'test:aws:batch:job:submit';

    protected function configure()
    {
        $this
            ->setDescription('Submit a job to AWS Batch')
        ;
    }

    /**
     * Replicated the execution of the command
     * aws --profile titodevops batch submit-job 
     *      --job-name version 
     *      --job-queue arn:aws:batch:ap-southeast-2:616022673352:job-queue/gromming-chimps-queue 
     *      --job-definition arn:aws:batch:ap-southeast-2:616022673352:job-definition/grooming-chimps-agent-job:1 
     *      --container-overrides '{"command": ["--version"], "environment": [{"name": "APP_ENV", "value": "prod"}]}'
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-batch-2016-08-10.html
     * @see https://docs.aws.amazon.com/cli/latest/reference/batch/index.html#cli-aws-batch
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $client = new BatchClient(['version' => '2016-08-10', 'region' => getenv('AWS_REGION_DEFAULT')]);
        $output = $client->submitJob([
            'containerOverrides' => [
                'command' => ['--version'],
                'environment' => [
                    ['name' => 'APP_ENV', 'value' => getenv('APP_ENV')]
                ],
            ],
            'jobDefinition' => getenv('AWS_BATCH_JOB_DEFINITION'),
            'jobName' => 'api',
            'jobQueue' => getenv('AWS_BATCH_JOB_QUEUE'),
        ]);

        $io->success($output);
    }
}
