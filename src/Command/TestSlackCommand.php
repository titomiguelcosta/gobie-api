<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

class TestSlackCommand extends Command
{
    protected static $defaultName = 'test:slack';
    protected $notifier;

    public function __construct(ChatterInterface $notifier)
    {
        $this->notifier = $notifier;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Post a slack message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $chat = new ChatMessage('Testing integration with slack');
        $chat->transport('slack');

        $this->notifier->send($chat);

        return 0;
    }
}
