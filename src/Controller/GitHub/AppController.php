<?php

namespace App\Controller\GitHub;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/github/app", name="github_status", methods={"POST", "GET"})
     */
    public function __invoke(Request $request)
    {
        $this->logger->critical('Debug GitHub app: ' . $request);

        return new Response('Welcomme to Gobie Github App.');
    }
}
