<?php

namespace App\MessageHandler;

use App\Entity\Request;
use App\Entity\Response;
use App\Entity\Tracking;
use App\Message\Tracking as Message;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class TrackingHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function __invoke(Message $message)
    {
        $httpRequest = $message->getRequest();
        $httpResponse = $message->getResponse();

        $tracking = new Tracking();
        $tracking->setRouteName($httpRequest->attributes->get('_route', 'unknown'));
        $tracking->setStartedAt($message->getStartedAt());
        $tracking->setTerminatedAt($message->getTerminatedAt());
        if ($message->getUserId()) {
            $tracking->setUser($this->userRepository->find($message->getUserId()));
        }
        $tracking->setNavigator($httpRequest->headers->get('User-Agent'));
        $tracking->setIpAddress($httpRequest->getClientIp());

        $request = new Request();
        $request->setQueryParameters($httpRequest->query->all());
        $request->setHeaders($httpRequest->headers->all());
        $request->setMethod($httpRequest->getRealMethod());
        $request->setFormat($httpRequest->getRequestFormat());
        $request->setBody($httpRequest->getContent());
        $request->setPathInfo($httpRequest->getPathInfo());
        $request->setLocale($httpRequest->getLocale());
        $request->setTracking($tracking);

        $response = new Response();
        $response->setStatusCode($httpResponse->getStatusCode());
        $response->setStatusText(HttpFoundationResponse::$statusTexts[$httpResponse->getStatusCode()] ?? 'unknown status');
        $response->setBody($response->getBody());
        $response->setTracking($tracking);

        $this->entityManager->persist($tracking);
        $this->entityManager->persist($request);
        $this->entityManager->persist($response);

        $this->entityManager->flush();
    }
}
