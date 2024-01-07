<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

class TestSlackCommand extends Command
{
    protected static $defaultName = 'test:slack';

    public function __construct(private ChatterInterface $notifier)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Post a slack message');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $chat = new ChatMessage('Testing integration with slack');
        $chat->transport('slack_builds');

        $this->notifier->send($chat);

        return Command::SUCCESS;
    }
}
