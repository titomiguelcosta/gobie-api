<?php

namespace App\Controller\GitHub;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    /**
     * @Route("/github/webhook", name="github_webhook", methods={"POST"})
     */
    public function __invoke()
    {
        return new Response('Got info.');
    }
}
