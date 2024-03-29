<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Request;
use App\Entity\Response;
use App\Entity\Tracking;
use App\Message\TrackingMessage;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class TrackingMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {
    }

    public function __invoke(TrackingMessage $message)
    {
        $tracking = new Tracking();
        $tracking->setRouteName($message->getRouteName());
        $tracking->setStartedAt($message->getStartedAt());
        $tracking->setTerminatedAt($message->getTerminatedAt());
        if ($message->getUserId()) {
            $tracking->setUser($this->userRepository->find($message->getUserId()));
        }
        $tracking->setNavigator($message->getNavigator());
        $tracking->setIpAddress($message->getIpAddress());

        $request = new Request();
        $request->setQueryParameters($message->getQueryParameters());
        $request->setHeaders($message->getRequestHeaders());
        $request->setMethod($message->getRequestMethod());
        $request->setFormat($message->getRequestFormat());
        $request->setBody($message->getRequestBody());
        $request->setPathInfo($message->getRequestPathInfo());
        $request->setLocale($message->getLocale());
        $request->setTracking($tracking);

        $response = new Response();
        $response->setStatusCode($message->getResponseStatusCode());
        $response->setStatusText($message->getResponseStatusText());
        $response->setBody($message->getResponseBody());
        $response->setTracking($tracking);

        $this->entityManager->persist($tracking);
        $this->entityManager->persist($request);
        $this->entityManager->persist($response);

        $this->entityManager->flush();
    }
}
