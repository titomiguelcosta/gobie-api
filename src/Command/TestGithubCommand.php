<?php

declare(strict_types=1);

namespace App\Command;

use Github\Client as GithubClient;
use Github\HttpClient\Builder as GithubBuilder;
use GuzzleHttp\Client as GuzzleClient;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestGithubCommand extends Command
{
    protected static $defaultName = 'test:github';

    public function __construct(private string $projectDir, private string $githubAppId)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Integration with GitHub');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $builder = new GithubBuilder(new GuzzleClient());
        $github = new GithubClient($builder, 'machine-man-preview');

        $jwt = (new Builder())
            ->issuedBy($this->githubAppId)
            ->issuedAt(new \DateTimeImmutable(time()))
            ->expiresAt(time() + 60)
            ->getToken(
                new Sha256(),
                new Key(sprintf('file://%s/%s', $this->projectDir, 'config/jwt/github.pem'))
            );

        $github->authenticate($jwt, null, GithubClient::AUTH_JWT);

        $token = $github->api('apps')->createInstallationToken(10751498);
        $github->authenticate($token['token'], null, GithubClient::AUTH_ACCESS_TOKEN);

        $io->success('Authentication was successful');

        $repos = $github->api('apps')->listRepositories();

        print_r($repos);

        $params = [
            'name' => 'testing integration with gobie',
            'head_sha' => '6bfde2a0cfcc721f8bea6ff3e9c6798cfb5a0a6c',
            // 'status' => 'in_progress',
            'conclusion' => 'success',
            'details_url' => 'https://gobie.titomiguelcosta.com/',
            'output' => [
                'title' => 'All good',
                'summary' => 'just started running checks',
            ],
        ];
        $check = $github->api('repo')->checks()->update('titomiguelcosta', 'hammer', 1047538484, $params);

        print_r($check);

        return Command::SUCCESS;
    }
}
