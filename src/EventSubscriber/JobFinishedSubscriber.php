<?php

namespace App\EventSubscriber;

use App\Entity\Job;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use LogicException;
use Swift_Mailer;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;

final class JobCompletedSubscriber implements EventSubscriber
{
    private $mailer;
    private $router;

    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function jobCompleted(CompletedEvent $event)
    {
        $job = $event->getSubject();
        if ($job instanceof Job) {
            $user = $job->getProject()->getCreatedBy();
            $message = (new \Swift_Message('Grooming Chimps: Job finished'))
                ->setFrom('groomingchimps@titomiguelcosta.com')
                ->setTo($user->getEmail())
                ->setBody(
                    sprintf(
                        'Job #%d finished. Check the report %s.',
                        $job->getId(),
                        'https://groomingchimps.titomiguelcosta.com/jobs/' . $job->getId()
                    ),
                    'text/plain'
                );

            $this->mailer->send($message);
        }
    }

    public function getSubscribedEvents()
    {
        return [
            'workflow.job.completed.finished',
        ];
    }
}
