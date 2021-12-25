<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends AbstractController
{
    /**
     * @Route("/status", name="status", methods={"GET"})
     * @IsGranted("PUBLIC_ACCESS")
     */
    public function __invoke()
    {
        return new JsonResponse(['status' => 'ok']);
    }
}
