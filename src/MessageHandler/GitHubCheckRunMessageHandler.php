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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(GitHubCheckRunMessage $message)
    {
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

        $checkRun = $this->entityManager->getRepository(CheckRun::class)->findOneBy(['checkId' => $message->getCheckId()]);

        if ($checkRun instanceof CheckRun) {
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
            ];

            $github->api('repo')->checkRuns()->update($checkRun->getUsername(), $checkRun->getRepo(), $params);

            $checkRun->setStatus(CheckRun::STATUS_SUCCEEDED);
            $this->entityManager->flush();
        }
    }
}
