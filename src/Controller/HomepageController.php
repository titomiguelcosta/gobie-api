<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class HomepageController extends AbstractController
{
    /**
     * @Route("/auth", name="auth")
     */
    public function __invoke(Request $request, Security $security)
    {
        return new JsonResponse(array_merge($request->headers->all(), ['me' => $security->getUser()->getUsername()]));
    }
}
