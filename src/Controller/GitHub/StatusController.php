<?php

namespace App\Controller\GitHub;

use Github\Client;
use Github\HttpClient\Builder as GitHubBuilder;
use GuzzleHttp\Client as GuzzleHttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class StatusController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * @Route("/github/status", name="github_status", methods={"POST"})
     */
    public function __invoke(Request $request)
    {
        $this->logger->critical('Debug GitHub request: ' . $request);

        return new Response('Got info.');
    }
}
