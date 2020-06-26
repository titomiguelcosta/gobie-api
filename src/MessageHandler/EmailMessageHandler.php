<?php

namespace App\MessageHandler;

use App\Message\EmailMessage;
use Swift_Mailer;
use Swift_Transport;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class EmailMessageHandler implements MessageHandlerInterface
{
    private $mailer;
    private $transport;

    public function __construct(Swift_Mailer $mailer, Swift_Transport $transport)
    {
        $this->mailer = $mailer;
        $this->transport = $transport;
    }

    public function __invoke(EmailMessage $message)
    {
        echo "about to send a message to " . $message->getTo();
        $message = (new \Swift_Message('Grooming Chimps: Sent from messenger'))
            ->setFrom('groomingchimps@titomiguelcosta.com')
            ->setTo($message->getTo())
            ->setBody(
                'Using messenger to send emails',
                'text/plain'
            );

        $this->mailer->send($message);

        $transport = $this->mailer->getTransport();
        if ($transport instanceof \Swift_Transport_SpoolTransport) {
            $spool = $transport->getSpool();
            $spool->flushQueue($this->transport);
        }
    }
}
