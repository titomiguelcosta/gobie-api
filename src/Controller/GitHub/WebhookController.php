<?php

namespace App\Controller\GitHub;

use App\Entity\GitHub\CheckRun;
use App\Entity\Job;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Github\Client as GithubClient;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Encoding\UnixTimestampDates;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class WebhookController extends AbstractController
{
    private $logger;
    private $entityManager;
    private $projectDir;
    private $githubAppId;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        string $projectDir,
        string $githubAppId
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
        $this->githubAppId = $githubAppId;
    }

    /**
     * @Route("/github/webhook", name="github_webhook", methods={"POST"})
     */
    public function __invoke(Request $request)
    {
        $body = $request->toArray();

        if (array_key_exists('check_suite', $body) && array_key_exists('action', $body) && 'requested' === $body['action']) {
            $this->logger->error('About to start GitHub check: ' . $this->githubAppId);

            $github = new GithubClient();

            $jwt = (new Builder(new JoseEncoder(), new UnixTimestampDates()))
                ->issuedBy($this->githubAppId)
                ->issuedAt(new \DateTimeImmutable("now", new \DateTimeZone("UTC")))
                ->expiresAt(new \DateTimeImmutable("+360 seconds", new \DateTimeZone("UTC")))
                ->getToken(
                    new Sha256(),
                    LocalFileReference::file(sprintf('file://%s/%s', $this->projectDir, 'config/jwt/github.pem'), '')
                );

            $github->authenticate($jwt->toString(), null, GithubClient::AUTH_JWT);

            $this->logger->error('GitHub authentication was successful');

            $jobRepository = $this->entityManager->getRepository(Job::class);

            // ToDo. this needs to be fixed.. we can not rely only on the branch name
            $jobs = $jobRepository->findBy([
                'branch' => $body['check_suite']['head_branch']
            ], null, 1);

            if (1 === count($jobs)) {
                $job = array_pop($jobs);
            }

            if ($job instanceof Job) {
                $copyJob = new Job();
                $copyJob->setProject($job->getProject());
                $copyJob->setBranch($job->getBranch());
                $copyJob->setEnvironment($job->getEnvironment());

                $this->entityManager->persist($copyJob);

                foreach ($job->getTasks() as $task) {
                    $copyTask = new Task();
                    $copyTask->setTool($task->getTool());
                    $copyTask->setOptions($task->getOptions());
                    $copyTask->setCommand($task->getCommand());

                    $copyJob->addTask($copyTask);

                    $this->entityManager->persist($copyTask);
                }

                $this->entityManager->flush();

                $params = [
                    'name' => 'Gobie',
                    'head_sha' => $body['check_suite']['head_branch'],
                    'status' => 'in_progress',
                    'conclusion' => 'success',
                    'details_url' => 'https://gobie.titomiguelcosta.com/jobs/' . $copyJob->getId(),
                    'output' => [
                        'title' => 'About to run checks',
                        'summary' => 'Gobie will take action',
                    ],
                ];

                $check = $github->api('repo')->checkRuns()->create($body['sender']['login'], $body['repository']['name'], $params);

                $this->logger->error('GitHub check run was created');

                $checkRun = new CheckRun();
                $checkRun->setJob($copyJob);
                $checkRun->setRepo($body['repository']['name']);
                $checkRun->setUsername($body['sender']['login']);
                $checkRun->setInstalationId($body['installation']['id']);
                $checkRun->setCheckId($check['id']);
                $checkRun->setStatus(CheckRun::STATUS_PENDING);

                $this->entityManager->persist($checkRun);

                $this->entityManager->flush();

                $this->logger->error('GitHub was stored');
            }
        }

        return new Response('Got info.');
    }
}
