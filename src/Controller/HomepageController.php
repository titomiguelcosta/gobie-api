<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class HomepageController extends AbstractController
{
    /**
     * @Route("/auth", name="auth")
     */
    public function __invoke(Security $security)
    {
        print_r($security->getUser()->getToken());
        print_r($security->getUser()->getUsername());
        die();
    }
}
