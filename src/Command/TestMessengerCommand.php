<?php

namespace App\Command;

use App\Message\EmailMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class TestMessengerCommand extends Command
{
    protected static $defaultName = 'test:messenger';
    protected $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a message to a queue using messenger');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->bus->dispatch(new EmailMessage('messenger@titomiguelcosta.com'));

        $io->success('message dispatched');

        return 0;
    }
}
