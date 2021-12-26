<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    public function __invoke(
        Request $request,
        UserRepository $userRepository,
        JWTTokenManagerInterface $tokenManager,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        $data = \json_decode($request->getContent(), true);

        if (
            JSON_ERROR_NONE !== \json_last_error()
            || !array_key_exists('username', $data)
            || !array_key_exists('password', $data)
        ) {
            throw new BadRequestHttpException('Invalid json format');
        }

        $user = $userRepository->findOneBy(['username' => $data['username']]);

        if (
            $user instanceof User
            && $userPasswordHasher->isPasswordValid($user, $data['password'])
        ) {
            return new JsonResponse([
                '@id' => '/users/' . $user->getUsername(),
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'token' => $tokenManager->create($user),
            ]);
        }

        throw new BadRequestHttpException('Invalid credentials');
    }
}
