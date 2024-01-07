<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\EmailMessage;
use App\Message\PusherMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class TestMessengerCommand extends Command
{
    protected static $defaultName = 'test:messenger';

    public function __construct(private MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send a message to a queue using messenger');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->bus->dispatch(new EmailMessage('messenger@titomiguelcosta.com'));
        $this->bus->dispatch(new PusherMessage('job-68', 'finished', ['job' => 68]));

        $io->success('message dispatched');

        return Command::SUCCESS;
    }
}
