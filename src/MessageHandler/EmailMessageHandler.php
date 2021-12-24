<?php

namespace App\MessageHandler;

use App\Message\EmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Email;

final class EmailMessageHandler implements MessageHandlerInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(EmailMessage $message)
    {
        $message = (new Email())
            ->subject('Gobie: Sent from messenger')
            ->from('gobie@titomiguelcosta.com')
            ->to($message->getTo())
            ->text('Using messenger to send emails')
            ->html('<p>Using messenger to send emails</p>');

        $this->mailer->send($message);
    }
}
