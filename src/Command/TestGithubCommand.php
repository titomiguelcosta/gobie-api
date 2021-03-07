<?php

namespace App\Command;

use Github\Client as GithubClient;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Encoding\UnixTimestampDates;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestGithubCommand extends Command
{
    protected static $defaultName = 'test:github';
    protected $projectDir;
    protected $githubAppId;

    public function __construct(string $projectDir, string $githubAppId)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
        $this->githubAppId = $githubAppId;
    }

    protected function configure()
    {
        $this
            ->setDescription('Integration with GitHub');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $github = new GithubClient();

        $jwt = (new Builder(new JoseEncoder(), new UnixTimestampDates()))
            ->issuedBy($this->githubAppId)
            ->issuedAt(new \DateTimeImmutable("now", new \DateTimeZone("UTC")))
            ->expiresAt(new \DateTimeImmutable("+360 seconds", new \DateTimeZone("UTC")))
            ->getToken(
                new Sha256(),
                LocalFileReference::file(sprintf('file://%s/%s', $this->projectDir, 'config/jwt/github.pem'), 'sfsdfds')
            );

        $github->authenticate($jwt->toString(), null, GithubClient::AUTH_JWT);

        $appInstalationDetails = $github->api('apps')->getInstallationForRepo('titomiguelcosta', 'hammer');
        $token = $github->api('apps')->createInstallationToken($appInstalationDetails["id"]);

        $github->authenticate($token['token'], null, GithubClient::AUTH_ACCESS_TOKEN);

        $io->success('Authentication was successful');

        $params = [
            'name' => 'testing integration with gobie',
            'head_sha' => 'c7a6e495551de62b7c3c40710bd74a4d60d4d5d0',
            //'status' => 'in_progress',
            'conclusion' => 'success',
            'details_url' => 'https://gobie.titomiguelcosta.com/',
            'output' => [
                'title' => 'I know the id of the instalation',
                'summary' => 'just started running checks',
            ],
        ];

        //$check = $github->api('repo')->checkRuns()->create('titomiguelcosta', 'hammer', $params);
        $check = $github->api('repo')->checkRuns()->update('titomiguelcosta', 'hammer', 2051856036, $params);

        print_r($check);

        return 0;
    }
}
