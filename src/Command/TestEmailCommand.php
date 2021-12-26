<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestEmailCommand extends Command
{
    protected static $defaultName = 'test:email';

    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send an e-mail');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $message = (new Email())
            ->subject('Gobie: Submit Job to AWS Batch')
            ->from('gobie@titomiguelcosta.com')
            ->to('titomiguelcosta@gmail.com')
            ->text('Job submitted to AWS Batch')
            ->html('<p>Job submitted to AWS Batch</p>');

        $output = $this->mailer->send($message);

        $io->success($output);

        return 0;
    }
}
