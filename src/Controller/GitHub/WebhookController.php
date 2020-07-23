<?php

namespace App\Controller\GitHub;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * @Route("/github/webhook", name="github_webhook", methods={"POST"})
     */
    public function __invoke(Request $request)
    {
        $this->logger->warning('Debug GitHub request: ' . $request);

        return new Response('Got info.');
    }
}
