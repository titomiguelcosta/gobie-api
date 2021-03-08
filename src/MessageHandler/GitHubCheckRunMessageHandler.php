<?php

namespace App\MessageHandler;

use App\Entity\GitHub\CheckRun;
use App\Message\GitHubCheckRunMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Github\Client as GithubClient;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Encoding\UnixTimestampDates;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha256;

final class GitHubCheckRunMessageHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $projectDir;
    private $githubAppId;

    public function __construct(EntityManagerInterface $entityManager, string $projectDir, string $githubAppId)
    {
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
        $this->githubAppId = $githubAppId;
    }

    public function __invoke(GitHubCheckRunMessage $message)
    {
        $checkRun = $this->entityManager->getRepository(CheckRun::class)->findOneBy(['checkId' => $message->getCheckId()]);

        if ($checkRun instanceof CheckRun) {
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
            $token = $github->api('apps')->createInstallationToken($checkRun->getInstalationId());
            $github->authenticate($token['token'], null, GithubClient::AUTH_ACCESS_TOKEN);

            $params = [
                'name' => 'Gobie',
                'head_sha' => $checkRun->getJob()->getCommitHash(),
                'status' => 'completed',
                'conclusion' => 'success',
                'details_url' => 'https://gobie.titomiguelcosta.com/jobs/' . $checkRun->getJob()->getId(),
                'output' => [
                    'title' => 'Finished with a status of ' . $checkRun->getJob()->getStatus(),
                    'summary' => 'Go to the website to see the output of the tasks.',
                ],
                //'completed_at' => $checkRun->getJob()->getFinishedAt()->format()
            ];

            $github->api('repo')->checkRuns()->update($checkRun->getUsername(), $checkRun->getRepo(), $checkRun->getCheckId(), $params);

            $checkRun->setStatus(CheckRun::STATUS_SUCCEEDED);
            $this->entityManager->flush();
        }
    }
}
