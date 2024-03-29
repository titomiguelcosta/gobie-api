<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Job;
use App\Entity\User;
use App\Message\EventMessage;
use App\Message\PusherMessage;
use App\Message\SlackMessage;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;

final class JobFinishedSubscriber implements EventSubscriber
{
    public function __construct(private MailerInterface $mailer, private MessageBusInterface $bus)
    {
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $job = $event->getObject();
        if ($job instanceof Job && Job::STATUS_FINISHED === $job->getStatus()) {
            $user = $job->getProject()->getCreatedBy();

            $this->doEmail($job, $user);

            $this->bus->dispatch(
                new PusherMessage('gobie.job.'.$job->getId(), Job::STATUS_FINISHED, ['job' => $job->getId()])
            );

            $this->bus->dispatch(
                new SlackMessage(
                    sprintf(
                        'Job #%d by %s just finished building.',
                        $job->getId(),
                        $user->getEmail()
                    ),
                    'builds'
                )
            );

            $eventMessage = new EventMessage();
            $eventMessage
                ->setName('job.finished')
                ->setAction(Job::STATUS_FINISHED)
                ->setEntityNamespace(Job::class)
                ->setEntityId($job->getId())
                ->setUserId($user->getId())
                ->setMessage(sprintf('Job #%d finished building.', $job->getId()))
                ->setLevel('info');
            $this->bus->dispatch($eventMessage);
        }
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postUpdate,
        ];
    }

    private function doEmail(Job $job, User $user): void
    {
        $message = (new Email())
            ->subject('Gobie: Job finished')
            ->from('gobie@titomiguelcosta.com')
            ->to($user->getEmail())
            ->text(
                sprintf(
                    'Job #%d finished. Check the report %s.',
                    $job->getId(),
                    'https://gobie.titomiguelcosta.com/jobs/'.$job->getId()
                )
            );

        $this->mailer->send($message);
    }
}
