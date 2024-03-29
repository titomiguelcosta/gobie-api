<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends AbstractController
{
    #[Route('/status', name: 'status', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function __invoke()
    {
        return new JsonResponse(['status' => 'ok']);
    }
}
